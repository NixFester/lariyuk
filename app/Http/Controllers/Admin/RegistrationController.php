<?php
// app/Http/Controllers/Admin/RegistrationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Event;
use App\Mail\TicketMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\PatternFill;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->get('event_id');
        
        // If filtering by specific event
        if ($eventId) {
            $event = Event::with(['categories' => function ($query) {
                $query->withCount('registrations');
            }])->findOrFail($eventId);
            
            return view('admin.registrations.index', compact('event', 'events'), ['singleEvent' => true]);
        }
        
        // Show all events
        $events = Event::with(['categories' => function ($query) {
            $query->withCount('registrations');
        }])->orderBy('title')->get();

        return view('admin.registrations.index', compact('events'), ['singleEvent' => false]);
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

        $registrations = $query->latest()->paginate(50)->withQueryString();

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

    //** export to csv for paid customers */
    public function exportDataForPaidCustomers(Request $request)
    {
        $query = Registration::with(['event', 'category'])
            ->where('payment_status', 'paid')
            ->latest();

        if ($eventId = $request->get('event_id')) {
            $query->where('event_id', $eventId);
        }

        $registrations = $query->get();

        $filename = 'pendaftar_lariyuk_paid_' . now()->format('Ymd_His') . '.csv';
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

    //** export to txt for paid customers with this format */
    //** [category] */
    //** [no], [nickname], [ukuran_kaos] */
    public function exportPaidCustomersAsText(Request $request)
    {
        $query = Registration::with(['event', 'category'])
            ->where('payment_status', 'paid')
            ->latest();

        if ($eventId = $request->get('event_id')) {
            $query->where('event_id', $eventId);
        }

        $registrations = $query->get();
        
        // Group by category
        $grouped = $registrations->groupBy(function ($r) {
            return $r->category?->name ?? 'Uncategorized';
        });

        $filename = 'pendaftar_lariyuk_paid_' . now()->format('Ymd_His') . '.txt';
        $headers  = [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($grouped) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            
            $rowNumber = 1;
            foreach ($grouped as $categoryName => $registrations) {
                fwrite($handle, "[{$categoryName}]\n");
                foreach ($registrations as $r) {
                    fwrite($handle, "{$rowNumber}, {$r->nickname}, {$r->ukuran_kaos}\n");
                    $rowNumber++;
                }
                fwrite($handle, "\n");
            }
            
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
    /** Show payment verification page */
    public function verification(Request $request)
    {
        // Registrations that need verification (WA confirmed)
        $queryVerified = Registration::with(['event', 'category'])
            ->where('payment_status', 'pending')
            ->whereNotNull('whatsapp_confirmed_at')
            ->latest();

        // Registrations pending WA confirmation
        $queryUnconfirmed = Registration::with(['event', 'category'])
            ->where('payment_status', 'pending')
            ->whereNull('whatsapp_confirmed_at')
            ->latest();

        if ($eventId = $request->get('event_id')) {
            $queryVerified->where('event_id', $eventId);
            $queryUnconfirmed->where('event_id', $eventId);
        }

        $registrations = $queryVerified->paginate(50, ['*'], 'page_verified')->withQueryString();
        $pendingRegistrations = $queryUnconfirmed->paginate(50, ['*'], 'page_pending')->withQueryString();
        $events = Event::orderBy('title')->get();

        return view('admin.registrations.verification', compact('registrations', 'pendingRegistrations', 'events'));
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

    /** Skip payment verification for registrations without WA confirmation */
    public function skipPayment(string $id)
    {
        $reg = Registration::findOrFail($id);

        if ($reg->payment_status === 'paid') {
            return redirect()->route('admin.registrations.verification')
                ->with('warning', 'Pembayaran sudah terverifikasi sebelumnya.');
        }

        if ($reg->whatsapp_confirmed_at) {
            return redirect()->route('admin.registrations.verification')
                ->with('warning', 'Pembayaran sudah dikonfirmasi via WhatsApp.');
        }

        $reg->update([
            'payment_status' => 'paid',
            'payment_verified_at' => now(),
        ]);

        // Send ticket via email after payment verification
        Mail::to($reg->email)->send(new TicketMail($reg));
        $reg->update(['ticket_email_sent' => true]);

        return redirect()->route('admin.registrations.verification')
            ->with('success', 'Pembayaran ' . $reg->invoice_number . ' berhasil dilewati. Tiket telah dikirim ke email.');
    }

public function exportToExcel(string $eventId)
{
    $event = Event::findOrFail($eventId);
    $registrations = Registration::where('event_id', $eventId)
                            ->where('payment_status', 'paid')
                            ->with(['event', 'category'])
                            ->get();

    // Create spreadsheet
    $spreadsheet = new Spreadsheet();

    // ===== SHEET 1: DETAIL PENDAFTAR =====
    $sheet1 = $spreadsheet->getActiveSheet();
    $sheet1->setTitle('Detail Pendaftar');

    // Headers
    $headers = [
        'No', 'Invoice', 'Nickname (BIB)', 'Nama Peserta', 'No. KTP', 'Email', 'No. HP',
        'Tgl Lahir', 'Jenis Kelamin', 'Golongan Darah', 'Ukuran Kaos', 'Kategori',
        'Kontak Darurat', 'HP Darurat'
    ];

    // Write headers
    $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
    foreach ($headers as $index => $header) {
        $sheet1->getCell($columns[$index] . '1')->setValue($header);
    }

    // Write registration data
    $row = 2;
    foreach ($registrations as $index => $r) {
        $sheet1->getCell('A' . $row)->setValue($index + 1);
        $sheet1->getCell('B' . $row)->setValue($r->invoice_number);
        $sheet1->getCell('C' . $row)->setValue($r->nickname ?? '-');
        $sheet1->getCell('D' . $row)->setValue($r->nama_peserta);
        $sheet1->getCell('E' . $row)->setValue($r->no_ktp);
        $sheet1->getCell('F' . $row)->setValue($r->email);
        $sheet1->getCell('G' . $row)->setValue($r->phone);
        $sheet1->getCell('H' . $row)->setValue($r->tanggal_lahir?->format('d/m/Y'));
        $sheet1->getCell('I' . $row)->setValue($r->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');
        $sheet1->getCell('J' . $row)->setValue($r->golongan_darah);
        $sheet1->getCell('K' . $row)->setValue($r->ukuran_kaos);
        $sheet1->getCell('L' . $row)->setValue($r->category?->name ?? '-');
        $sheet1->getCell('M' . $row)->setValue($r->kontak_darurat_nama);
        $sheet1->getCell('N' . $row)->setValue($r->kontak_darurat_hp);
        $row++;
    }

    // Auto-size columns
    foreach ($columns as $col) {
        $sheet1->getColumnDimension($col)->setAutoSize(true);
    }

    // ===== SHEET 2: REKAPAN KATEGORI =====
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Rekapan Kategori');

    // Headers for sheet 2
    $sheet2->getCell('A1')->setValue('Kategori');
    $sheet2->getCell('B1')->setValue('Jumlah Peserta');

    // Group registrations by category
    $categoryStats = $registrations
        ->groupBy('event_category_id')
        ->map(function ($group) {
            return [
                'name' => $group->first()?->category?->name ?? 'Uncategorized',
                'count' => $group->count(),
            ];
        })
        ->sortBy('name')
        ->values();

    // Write category data
    $row = 2;
    foreach ($categoryStats as $stat) {
        $sheet2->getCell('A' . $row)->setValue($stat['name']);
        $sheet2->getCell('B' . $row)->setValue($stat['count']);
        $row++;
    }

    // Auto-size columns for sheet 2
    $sheet2->getColumnDimension('A')->setAutoSize(true);
    $sheet2->getColumnDimension('B')->setAutoSize(true);
    // ===== SHEET 3: DAFTAR PESERTA (NO, NICKNAME, UKURAN KAOS) =====
    $sheet3 = $spreadsheet->createSheet();
    $sheet3->setTitle('Daftar Peserta');

    // Headers for sheet 3
    $sheet3->getCell('A1')->setValue('No');
    $sheet3->getCell('B1')->setValue('Nickname');
    $sheet3->getCell('C1')->setValue('Ukuran Kaos');

    // Write data
    $row = 2;
    foreach ($registrations as $index => $r) {
        $sheet3->getCell('A' . $row)->setValue($index + 1);
        $sheet3->getCell('B' . $row)->setValue($r->nickname ?? '-');
        $sheet3->getCell('C' . $row)->setValue($r->ukuran_kaos);
        $row++;
    }

    // Auto-size columns for sheet 3
    $sheet3->getColumnDimension('A')->setAutoSize(true);
    $sheet3->getColumnDimension('B')->setAutoSize(true);
    $sheet3->getColumnDimension('C')->setAutoSize(true);

    // ===== SHEET 4: REKAPAN UKURAN KAOS =====
    $sheet4 = $spreadsheet->createSheet();
    $sheet4->setTitle('Rekapan Ukuran Kaos');

    // Headers for sheet 4
    $sheet4->getCell('A1')->setValue('Ukuran Kaos');
    $sheet4->getCell('B1')->setValue('Jumlah Peserta');

    // Group registrations by ukuran kaos
    $sizeStats = $registrations
        ->groupBy('ukuran_kaos')
        ->map(function ($group) {
            return [
                'size' => $group->first()?->ukuran_kaos ?? 'Tidak Ada',
                'count' => $group->count(),
            ];
        })
        ->sortBy('size')
        ->values();

    // Write size data
    $row = 2;
    foreach ($sizeStats as $stat) {
        $sheet4->getCell('A' . $row)->setValue($stat['size']);
        $sheet4->getCell('B' . $row)->setValue($stat['count']);
        $row++;
    }

    // Auto-size columns for sheet 4
    $sheet4->getColumnDimension('A')->setAutoSize(true);
    $sheet4->getColumnDimension('B')->setAutoSize(true);

    // Output Excel file
    $filename = 'pendaftar_' . $event->slug . '_' . now()->format('Ymd_His') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
}
