<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;

class RacepackController extends Controller
{
    /**
     * Show the racepack monitor page with QR scan and list of taken racepacks
     */
    public function monitor()
    {
        $registrations = Registration::where('is_taken', 1)
            ->with(['event', 'category'])
            ->orderBy('updated_at', 'desc')
            ->paginate(50);

        return view('admin.racepack.monitor', compact('registrations'));
    }

    /**
     * Show registration details for racepack confirmation
     */
    public function show($invoice)
    {
        $registration = Registration::with(['event', 'category'])
            ->where('invoice_number', $invoice)
            ->firstOrFail();
        
        // Calculate BIB dynamically
        $bibNumber = Registration::where('event_id', $registration->event_id)
            ->where('payment_status', 'paid')
            ->where('id', '<=', $registration->id)
            ->count() + 10000;

        return view('admin.racepack.show', compact('registration', 'bibNumber'));
    }

    /**
     * Confirm a racepack has been taken
     */
    public function confirm(Request $request, $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

        if ($registration->is_taken) {
            return redirect()->route('admin.racepack.monitor')
                ->with('success', "Racepack untuk {$registration->nama_peserta} sudah ditandai diambil.");
        }

        $registration->update([
            'is_taken' => true,
        ]);

        return redirect()->route('admin.racepack.monitor')
            ->with('success', "Racepack untuk {$registration->nama_peserta} berhasil ditandai sebagai diambil.");
    }
}
