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
    /**
     * Normalize Indonesian phone numbers:
     * - Convert 08xxx to +628xxx
     * - Keep +62xxx as is
     * - If number doesn't match patterns, return as is
     */
    private function normalizePhoneNumber(string $phone): string
    {
        $phone = trim(preg_replace('/\s+/', '', $phone));
        
        // Already has +62 prefix
        if (str_starts_with($phone, '+62')) {
            return $phone;
        }
        
        // Convert 08xxx to +628xxx
        if (str_starts_with($phone, '08')) {
            return '+62' . substr($phone, 1);
        }
        
        // Return as is if it doesn't match patterns
        return $phone;
    }

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
            'XXS' => [
                'normal' => ['width' => '46', 'length' => '60'],
            ],
            'XS' => [
                'normal' => ['width' => '48', 'length' => '62'],
                'sport' => ['width' => '46', 'length' => '60'],
            ],
            'S' => [
                'normal' => ['width' => '50', 'length' => '65'],
                'sport' => ['width' => '48', 'length' => '62'],
            ],
            'M' => [
                'normal' => ['width' => '52', 'length' => '68'],
                'sport' => ['width' => '50', 'length' => '65'],
            ],
            'L' => [
                'normal' => ['width' => '54', 'length' => '70'],
                'sport' => ['width' => '52', 'length' => '68'],
            ],
            'XL' => [
                'normal' => ['width' => '56', 'length' => '72'],
                'sport' => ['width' => '54', 'length' => '70'],
            ],
            '2XL' => [
                'normal' => ['width' => '58', 'length' => '74'],
                'sport' => ['width' => '56', 'length' => '72'],
            ],
            '3XL' => [
                'normal' => ['width' => '60', 'length' => '78'],
                'sport' => ['width' => '58', 'length' => '74'],
            ],
            '4XL' => [
                'normal' => ['width' => '62', 'length' => '80'],
                'sport' => ['width' => '60', 'length' => '78'],
            ],
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
            'ukuran_kaos'         => 'required|in:XXS,XS,XS-sport,S,S-sport,M,M-sport,L,L-sport,XL,XL-sport,2XL,2XL-sport,3XL,3XL-sport,4XL,4XL-sport',
            'golongan_darah'      => 'required|in:A,B,AB,O,A+,A-,B+,B-,AB+,AB-,O+,O-',
            'kontak_darurat_nama' => 'required|string|max:150',
            'kontak_darurat_hp'   => 'required|string|max:20',
        ]);

        $event    = Event::findOrFail($data['event_id']);
        $category = EventCategory::findOrFail($data['event_category_id']);

        // Check if category has available slots
        if (!$category->hasAvailableSlots()) {
            return redirect()->back()
                ->with('error', 'Maaf, kuota untuk kategori ' . $category->name . ' telah penuh. Silakan pilih kategori lain.')
                ->withInput();
        }

        // ── Normalize phone numbers ─────────────────────────────────────
        $data['phone'] = $this->normalizePhoneNumber($data['phone']);
        $data['kontak_darurat_hp'] = $this->normalizePhoneNumber($data['kontak_darurat_hp']);
        // ────────────────────────────────────────────────────────────────

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

    /**
     * API endpoint to check category availability
     * Used by JavaScript frontend for real-time validation
     */
    public function checkCategoryAvailability(int $categoryId)
    {
        try {
            $category = EventCategory::with('event')
                ->findOrFail($categoryId);

            $registrationCount = $category->getRegistrationCount();
            $availableSlots = $category->getAvailableSlots();
            $hasSlots = $category->hasAvailableSlots();

            return response()->json([
                'id' => $category->id,
                'name' => $category->name,
                'limit' => $category->limit,
                'registration_count' => $registrationCount,
                'available_slots' => $availableSlots,
                'has_slots' => $hasSlots,
                'message' => $hasSlots 
                    ? "Sisa kuota: {$availableSlots} dari {$category->limit}"
                    : "Maaf, kuota untuk kategori '{$category->name}' telah penuh",
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking category availability', [
                'category_id' => $categoryId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Gagal memeriksa ketersediaan kategori',
            ], 500);
        }
    }

    /**
     * Get all categories with their availability for an event
     */
    public function getEventCategoriesAvailability(string $eventSlug)
    {
        try {
            $event = Event::where('slug', $eventSlug)
                ->with('categories')
                ->firstOrFail();

            $categoriesWithAvailability = $event->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'normal_price' => $category->normal_price,
                    'early_bird_price' => $category->early_bird_price,
                    'limit' => $category->limit,
                    'registration_count' => $category->getRegistrationCount(),
                    'available_slots' => $category->getAvailableSlots(),
                    'has_slots' => $category->hasAvailableSlots(),
                ];
            });

            return response()->json([
                'event_id' => $event->id,
                'event_slug' => $event->slug,
                'event_title' => $event->title,
                'categories' => $categoriesWithAvailability,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching event categories availability', [
                'event_slug' => $eventSlug,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Gagal mengambil data kategori event',
            ], 500);
        }
    }
}
