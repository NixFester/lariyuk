<?php
// app/Http/Controllers/Admin/RegistrationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Event;
use App\Mail\TicketMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        // Get all events with their categories and registration counts
        $events = Event::with(['categories' => function ($query) {
            $query->withCount('registrations');
        }])->orderBy('title')->get();

        return view('admin.registrations.index', compact('events'));
    }

    /**
     * Show registrations grouped by event and category
     */
    public function byEvent(Request $request, string $eventId)
    {
        $event = Event::with(['categories' => function ($query) {
            $query->withCount('registrations');
        }])->findOrFail($eventId);

        return view('admin.registrations.by-event', compact('event'));
    }

    /**
     * Show all registrations for a specific event category
     */
    public function byCategory(Request $request, string $eventId, string $categoryId)
    {
        $event = Event::findOrFail($eventId);
        $category = $event->categories()->findOrFail($categoryId);

        $query = Registration::where('event_id', $eventId)
                            ->where('event_category_id', $categoryId)
                            ->with(['event', 'category']);

        if ($status = $request->get('status')) {
            $query->where('payment_status', $status);
        }
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%{$search}%")
                  ->orWhere('no_ktp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        $registrations = $query->latest()->paginate(20)->withQueryString();

        return view('admin.registrations.by-category', compact('event', 'category', 'registrations'));
    }

    /**
     * Export registrations for a specific category as CSV
     */
    public function exportByCategory(Request $request, string $eventId, string $categoryId)
    {
        $event = Event::findOrFail($eventId);
        $category = $event->categories()->findOrFail($categoryId);

        $registrations = Registration::where('event_id', $eventId)
                                     ->where('event_category_id', $categoryId)
                                     ->with(['event', 'category'])
                                     ->latest()
                                     ->get();

        $filename = 'pendaftar_' . $event->slug . '_' . $category->id . '_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($handle, [
                'No','Invoice','Nickname (BIB)','Nama Peserta','No. KTP','Email','No. HP',
                'Tgl Lahir','Jenis Kelamin','Golongan Darah','Ukuran Kaos',
                'Event','Kategori','Kontak Darurat','HP Darurat',
                'Harga Tiket','Biaya Admin','Total','Early Bird',
                'Metode Bayar','Status','Tgl Daftar',
                'Email Tiket Terkirim',
            ]);
            foreach ($registrations as $i => $r) {
                fputcsv($handle, [
                    $i + 1,
                    $r->invoice_number,
                    $r->nickname ?? '-',
                    $r->nama_peserta,
                    $r->no_ktp,
                    $r->email,
                    $r->phone,
                    $r->tanggal_lahir?->format('d/m/Y'),
                    $r->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                    $r->golongan_darah,
                    $r->ukuran_kaos,
                    $r->event?->title,
                    $r->category?->name,
                    $r->kontak_darurat_nama,
                    $r->kontak_darurat_hp,
                    $r->subtotal,
                    $r->admin_fee,
                    $r->total,
                    $r->is_early_bird ? 'Ya' : 'Tidak',
                    $r->payment_method ?? '-',
                    $r->payment_status,
                    $r->created_at?->format('d/m/Y H:i'),
                    $r->ticket_email_sent ? 'Ya' : 'Tidak',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(string $id)
    {
        $registration = Registration::with(['event', 'category'])->findOrFail($id);
        return view('admin.registrations.show', compact('registration'));
    }

    public function destroy(string $id)
    {
        $reg = Registration::findOrFail($id);
        if ($reg->payment_status === 'paid') {
            $reg->event?->decrement('registered');
        }
        $reg->delete();
        return redirect()->route('admin.registrations.index')
            ->with('success', 'Data pendaftar berhasil dihapus.');
    }

    /** Export as CSV – opens perfectly in Excel / Google Sheets */
    public function export(Request $request)
    {
        $query = Registration::with(['event', 'category'])->latest();
        if ($eventId = $request->get('event_id')) $query->where('event_id', $eventId);
        if ($status  = $request->get('status'))   $query->where('payment_status', $status);
        $registrations = $query->get();

        $filename = 'pendaftar_lariyuk_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($registrations) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($handle, [
                'No','Invoice','Nickname (BIB)','Nama Peserta','No. KTP','Email','No. HP',
                'Tgl Lahir','Jenis Kelamin','Golongan Darah','Ukuran Kaos',
                'Event','Kategori','Kontak Darurat','HP Darurat',
                'Harga Tiket','Biaya Admin','Total','Early Bird',
                'Metode Bayar','Status','Tgl Daftar',
                'Email Tiket Terkirim',
            ]);
            foreach ($registrations as $i => $r) {
                fputcsv($handle, [
                    $i + 1,
                    $r->invoice_number,
                    $r->bib_number ?? '-',
                    $r->nama_peserta,
                    $r->no_ktp,
                    $r->email,
                    $r->phone,
                    $r->tanggal_lahir?->format('d/m/Y'),
                    $r->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
                    $r->golongan_darah,
                    $r->ukuran_kaos,
                    $r->event?->title,
                    $r->category?->name,
                    $r->kontak_darurat_nama,
                    $r->kontak_darurat_hp,
                    $r->subtotal,
                    $r->admin_fee,
                    $r->total,
                    $r->is_early_bird ? 'Ya' : 'Tidak',
                    $r->payment_method ?? '-',
                    $r->payment_status,
                    $r->created_at?->format('d/m/Y H:i'),
                    $r->ticket_email_sent ? 'Ya' : 'Tidak',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /** Show payment verification page */
    public function verification(Request $request)
    {
        $query = Registration::with(['event', 'category'])
            ->where('payment_status', 'pending')
            ->whereNotNull('whatsapp_confirmed_at')
            ->latest();

        if ($eventId = $request->get('event_id')) {
            $query->where('event_id', $eventId);
        }

        $registrations = $query->paginate(20)->withQueryString();
        $events = Event::orderBy('title')->get();

        return view('admin.registrations.verification', compact('registrations', 'events'));
    }

    /** Verify payment and mark as paid */
    public function verifyPayment(string $id)
    {
        $reg = Registration::findOrFail($id);
        
        if ($reg->payment_status === 'paid') {
            return redirect()->route('admin.registrations.verification')
                ->with('warning', 'Pembayaran sudah terverifikasi sebelumnya.');
        }

        $reg->update([
            'payment_status' => 'paid',
            'payment_verified_at' => now(),
        ]);

        // Send ticket via email after payment verification
        Mail::to($reg->email)->send(new TicketMail($reg));
        $reg->update(['ticket_email_sent' => true]);

        return redirect()->route('admin.registrations.verification')
            ->with('success', 'Pembayaran ' . $reg->invoice_number . ' berhasil diverifikasi. Tiket telah dikirim ke email.');
    }
}
