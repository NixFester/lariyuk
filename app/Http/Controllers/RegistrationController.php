<?php
namespace App\Http\Controllers;

use App\Mail\TicketMail;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\PaymentMethod;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    /** Show checkout form */
    public function show(string $slug, string $categoryId)
    {
        $event    = Event::with(['categories', 'highlights'])->where('slug', $slug)->firstOrFail();
        $category = EventCategory::where('id', $categoryId)
                      ->where('event_id', $event->id)->firstOrFail();

        $price       = $category->active_price;
        $isEarlyBird = $event->is_early_bird_active;
        $adminFee    = 5000;
        $total       = $price + $adminFee;

        $shirtSizes = [
            'XS'  => ['chest' => '82–87',   'length' => '65'],
            'S'   => ['chest' => '88–93',   'length' => '67'],
            'M'   => ['chest' => '94–99',   'length' => '69'],
            'L'   => ['chest' => '100–105', 'length' => '71'],
            'XL'  => ['chest' => '106–111', 'length' => '73'],
            'XXL' => ['chest' => '112–117', 'length' => '75'],
        ];

        return view('checkout.show', compact(
            'event', 'category', 'price', 'isEarlyBird', 'adminFee', 'total', 'shirtSizes'
        ));
    }

    /** Process registration */
    public function store(Request $request)
    {
        $data = $request->validate([
            'event_id'            => 'required|exists:events,id',
            'event_category_id'   => 'required|exists:event_categories,id',
            'no_ktp'              => 'required|digits:16',
            'nama_peserta'        => 'required|string|max:150',
            'nickname'            => 'required|string|max:30',   // BIB nickname — free, not unique
            'email'               => 'required|email',
            'phone'               => 'required|string|max:20',
            'tanggal_lahir'       => 'required|date',
            'jenis_kelamin'       => 'required|in:L,P',
            'ukuran_kaos'         => 'required|in:XS,S,M,L,XL,XXL',
            'golongan_darah'      => 'required|in:A,B,AB,O,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'kontak_darurat_nama' => 'required|string|max:150',
            'kontak_darurat_hp'   => 'required|string|max:20',
        ]);

        $event    = Event::findOrFail($data['event_id']);
        $category = EventCategory::findOrFail($data['event_category_id']);

        $isEarlyBird = $event->is_early_bird_active;
        $price       = $category->active_price;
        $adminFee    = 5000;
        $total       = $price + $adminFee;

        $registration = DB::transaction(function () use ($data, $event, $isEarlyBird, $price, $adminFee, $total) {
            $reg = Registration::create([
                ...$data,
                'invoice_number' => Registration::generateInvoice(),
                'payment_status' => 'pending',   // User must confirm via WhatsApp within 10 mins
                'is_early_bird'  => $isEarlyBird,
                'subtotal'       => $price,
                'admin_fee'      => $adminFee,
                'total'          => $total,
            ]);

            $event->increment('registered');

            return $reg;
        });

        // ── TICKET EMAIL (TEST MODE) ─────────────────────────────────────
        // In .env set MAIL_MAILER=log to "send" to storage/logs/laravel.log
        // Change to MAIL_MAILER=smtp + real credentials to send real emails.
        // NOTE: For IPaymu & Midtrans flow, ticket is sent after payment confirmation.
        // For classic flow, ticket is sent here (can be changed to post-verification)
        if (!in_array(config('payment.gateway'), ['ipaymu', 'midtrans'])) {
            try {
                Mail::to($registration->email)->send(new TicketMail($registration));
                $registration->update(['ticket_email_sent' => true]);
                Log::info("Ticket email sent (test) to {$registration->email} — {$registration->invoice_number}");
            } catch (\Exception $e) {
                $errorMsg = $e->getMessage();
                $stackTrace = $e->getTraceAsString();
                
                Log::channel('single')->error("TICKET_EMAIL_FAILED_REGISTRATION", [
                    'invoice' => $registration->invoice_number,
                    'email' => $registration->email,
                    'error' => $errorMsg,
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $stackTrace,
                ]);

                \file_put_contents(
                    storage_path('logs/email_errors.log'),
                    "[" . now()->toDateTimeString() . "] REGISTRATION: Invoice: {$registration->invoice_number} | Email: {$registration->email} | Error: {$errorMsg}\n" . $stackTrace . "\n" . str_repeat("-", 80) . "\n",
                    FILE_APPEND
                );
            }
        }
        // ────────────────────────────────────────────────────────────────

        // Cache visitor data for 24 hours to show notification bell
        Cache::put(
            'registration_' . $registration->id,
            [
                'invoice_number' => $registration->invoice_number,
                'nama_peserta' => $registration->nama_peserta,
                'email' => $registration->email,
                'phone' => $registration->phone,
                'payment_status' => $registration->payment_status,
            ],
            now()->addHours(24)
        );

        // Route based on payment gateway configuration
        $gateway = config('payment.gateway');
        Log::info('Payment gateway routing', ['gateway' => $gateway, 'invoice' => $registration->invoice_number]);
        
        if ($gateway === 'ipaymu') {
            Log::info('Routing to IPaymu', ['invoice' => $registration->invoice_number]);
            return redirect()->route('checkout.ipaymu.initiate', $registration->invoice_number);
        }

        if ($gateway === 'midtrans') {
            Log::info('Routing to Midtrans', ['invoice' => $registration->invoice_number]);
            return redirect()->route('checkout.midtrans.initiate', $registration->invoice_number);
        }

        Log::info('Routing to classic', ['invoice' => $registration->invoice_number]);
        return redirect()->route('checkout.pending', $registration->invoice_number);
    }

    /** Pending payment page */
    public function pending(string $invoice)
    {
        $registration = Registration::with(['event', 'category'])
            ->where('invoice_number', $invoice)->firstOrFail();

        // If payment is already verified, redirect to success page
        if ($registration->payment_status === 'paid') {
            return redirect()->route('checkout.success', $invoice);
        }

        // Update cache with latest status
        Cache::put(
            'registration_' . $registration->id,
            [
                'invoice_number' => $registration->invoice_number,
                'nama_peserta' => $registration->nama_peserta,
                'email' => $registration->email,
                'phone' => $registration->phone,
                'payment_status' => $registration->payment_status,
            ],
            now()->addHours(24)
        );

        $paymentMethods = PaymentMethod::active()->get();

        return view('checkout.pending', compact('registration', 'paymentMethods'));
    }

    /** Cancel registration */
    public function cancelRegistration(string $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

        if ($registration->payment_status === 'paid') {
            return redirect()->route('checkout.pending', $invoice)
                ->with('error', 'Tidak dapat membatalkan pendaftaran yang sudah terbayar.');
        }

        // Decrement event registered count if not yet paid
        $registration->event->decrement('registered');

        // Delete registration
        $registration->delete();

        return redirect()->route('home')
            ->with('success', 'Pendaftaran telah dibatalkan.');
    }

    /** Success / ticket display page */
    public function success(string $invoice)
    {
        $registration = Registration::with(['event', 'category'])
            ->where('invoice_number', $invoice)->firstOrFail();
        

        return view('checkout.success', compact('registration'));
    }

    //**workaround from midtrans */
    /** Success / ticket display page workaround */
    public function successmidtrans(string $invoice)
    {
        $registration = Registration::with(['event', 'category'])
            ->where('invoice_number', $invoice)->firstOrFail();
        
            // Verify payment
        $registration->update([
            'payment_status' => 'paid',
            'payment_verified_at' => now(),
        ]);


        return view('checkout.success', compact('registration'));
    }

    /** Confirm payment via WhatsApp */
    public function confirmPayment(string $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();
        
        // Only allow confirmation if payment_status is still 'pending'
        if ($registration->payment_status !== 'pending') {
            return redirect()->route('home')
                ->with('error', 'Pembayaran sudah pada status: ' . ucfirst($registration->payment_status));
        }
        
        // Mark as WhatsApp confirmed — payment still pending admin verification
        $registration->update([
            'whatsapp_confirmed_at' => now(),
        ]);

        return redirect()->route('home')
            ->with('success', 'Konfirmasi pembayaran diterima! Pendaftaran Anda akan diverifikasi oleh admin.');
    }

    /** Resend ticket email */
    public function resendTicket(string $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

        try {
            Mail::to($registration->email)->send(new TicketMail($registration));
            $registration->update(['ticket_email_sent' => true]);
            Log::info("Ticket email resent to {$registration->email} — {$registration->invoice_number}");

            return response()->json([
                'success' => true,
                'message' => 'Tiket berhasil dikirim ke email Anda'
            ]);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            $stackTrace = $e->getTraceAsString();
            
            // Log to both error channel and custom email channel
            Log::channel('single')->error("TICKET_EMAIL_FAILED", [
                'invoice' => $registration->invoice_number,
                'email' => $registration->email,
                'error' => $errorMsg,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $stackTrace,
                'time' => now()->toDateTimeString(),
            ]);

            // Also write to a dedicated email error log
            \file_put_contents(
                storage_path('logs/email_errors.log'),
                "[" . now()->toDateTimeString() . "] Invoice: {$invoice} | Email: {$registration->email} | Error: {$errorMsg}\n" . $stackTrace . "\n" . str_repeat("-", 80) . "\n",
                FILE_APPEND
            );

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim tiket. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    /** Get notifications for visitor */
    public function getNotifications(Request $request)
    {
        $registrationId = $request->query('registration_id');
        $invoice = $request->query('invoice');
        
        $registration = null;
        
        // Try to find registration by ID or invoice
        if ($registrationId) {
            $registration = Registration::find($registrationId);
        } elseif ($invoice) {
            $registration = Registration::where('invoice_number', $invoice)->first();
        }
        
        if (!$registration) {
            return response()->json(['notifications' => [], 'count' => 0, 'registration' => null]);
        }

        $notifications = [];

        if ($registration->payment_status === 'paid') {
            $notifications[] = [
                'type' => 'success',
                'title' => 'Pembayaran Terverifikasi! ✓',
                'message' => 'Pendaftaran Anda telah diverifikasi oleh admin. Anda akan menerima email tiket dalam beberapa saat.',
                'timestamp' => $registration->payment_verified_at?->format('d M Y H:i'),
            ];
        } elseif ($registration->payment_status === 'pending' && $registration->whatsapp_confirmed_at) {
            $notifications[] = [
                'type' => 'pending',
                'title' => 'Menunggu Verifikasi Admin',
                'message' => 'Pembayaran Anda sudah dikonfirmasi. Admin akan memverifikasi pembayaran Anda dalam waktu 1x24 jam.',
                'timestamp' => $registration->whatsapp_confirmed_at?->format('d M Y H:i'),
            ];
        } elseif ($registration->payment_status === 'pending') {
            $notifications[] = [
                'type' => 'warning',
                'title' => 'Menunggu Pembayaran',
                'message' => 'Silakan lakukan pembayaran sesuai metode pembayaran yang Anda pilih. Klik "Sudah Bayar" setelah transfer selesai.',
                'timestamp' => $registration->created_at?->format('d M Y H:i'),
            ];
        } elseif ($registration->payment_status === 'expired') {
            $notifications[] = [
                'type' => 'error',
                'title' => 'Pembayaran Kadaluarsa',
                'message' => 'Waktu pembayaran Anda telah habis. Silakan lakukan pendaftaran ulang.',
                'timestamp' => null,
            ];
        }

        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications),
            'registration' => [
                'id' => $registration->id,
                'invoice_number' => $registration->invoice_number,
                'nama_peserta' => $registration->nama_peserta,
                'payment_status' => $registration->payment_status,
                'payment_method' => $registration->payment_method,
            ]
        ]);
    }

    /** Verify registration via WhatsApp link (requires login) */
    public function verifyRegistrationLink(string $invoice)
    {
        // Check if user is authenticated as admin
        if (!auth('admin')->check()) {
            // Redirect to admin login with message
            return redirect()->route('admin.login')
                ->with('message', 'Silakan login sebagai admin terlebih dahulu untuk memverifikasi pembayaran.');
        }

        // Get registration
        $registration = Registration::where('invoice_number', $invoice)
            ->where('payment_status', 'pending')
            ->firstOrFail();

        // Verify payment
        $registration->update([
            'payment_status' => 'paid',
            'payment_verified_at' => now(),
        ]);

        // Send ticket email
        try {
            Mail::to($registration->email)->send(new TicketMail($registration));
            $registration->update(['ticket_email_sent' => true]);
            Log::info("Ticket email sent via verification link to {$registration->email} — {$invoice}");
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            $stackTrace = $e->getTraceAsString();
            
            Log::channel('single')->error("TICKET_EMAIL_FAILED_VERIFICATION", [
                'invoice' => $invoice,
                'email' => $registration->email,
                'error' => $errorMsg,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $stackTrace,
            ]);

            \file_put_contents(
                storage_path('logs/email_errors.log'),
                "[" . now()->toDateTimeString() . "] VERIFICATION: Invoice: {$invoice} | Email: {$registration->email} | Error: {$errorMsg}\n" . $stackTrace . "\n" . str_repeat("-", 80) . "\n",
                FILE_APPEND
            );
        }

        return redirect()->route('admin.registrations.verification')
            ->with('success', "Pembayaran {$registration->nama_peserta} ({$invoice}) berhasil diverifikasi!");
    }
}
