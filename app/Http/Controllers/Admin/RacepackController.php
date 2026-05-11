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
     * Mark a racepack as taken by invoice number
     */
    public function markAsTaken(Request $request, $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();
        
        $registration->update([
            'is_taken' => true,
        ]);

        return redirect()->route('admin.racepack.monitor')
            ->with('success', "Racepack for {$registration->nama_peserta} ({$invoice}) marked as taken!");
    }
}
