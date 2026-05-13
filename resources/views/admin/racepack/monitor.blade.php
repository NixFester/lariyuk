@extends('layouts.admin')
@section('title','Monitoring Pengambilan Racepack')
@section('admin-content')

<div class="mb-8">
    <h1 class="font-display font-bold text-3xl text-slate-900 mb-2">Monitoring Pengambilan Racepack</h1>
    <p class="text-slate-600">Scan QR code atau masukkan invoice untuk melihat detail dan mengonfirmasi pengambilan racepack</p>
</div>

{{-- Success Message --}}
@if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- QR Scanner Section --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 overflow-hidden">
        
        <div class="p-6">
            <div id="scannerContainer" class="hidden">
                <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto; border-radius: 12px; overflow: hidden; background: #000;"></div>
                <p class="text-sm text-slate-600 text-center mt-3">Arahkan kamera ke QR code pada invoice</p>
            </div>
            
            {{-- Manual Input --}}
            <div class="mt-6 pt-6 border-t border-gray-100">
                <label class="block text-sm font-medium text-slate-700 mb-2">Masukkan No. Invoice Secara Manual</label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="invoiceInput" 
                        placeholder="Contoh: INV-20260510-ABC123"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    <button onclick="submitInvoice()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Section --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-display font-bold text-slate-800">Statistik</h2>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <p class="text-sm text-slate-600 mb-1">Total Diambil Hari Ini</p>
                <p class="text-3xl font-display font-bold text-primary-600">{{ $registrations->total() }}</p>
            </div>
            <div class="pt-4 border-t border-gray-100">
                <p class="text-xs text-slate-500">Halaman {{ $registrations->currentPage() }} dari {{ $registrations->lastPage() }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Registrations Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-display font-bold text-slate-800">Daftar Racepack yang Diambil</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left">
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">No.</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Invoice</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Nama Peserta</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Event</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Kategori</th>
                    <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Waktu Diambil</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($registrations as $index => $registration)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-slate-600">{{ ($registrations->currentPage() - 1) * 50 + $index + 1 }}</td>
                        <td class="px-6 py-3 font-mono font-bold text-primary-600">{{ $registration->invoice_number }}</td>
                        <td class="px-6 py-3 font-medium text-slate-900">{{ $registration->nama_peserta }}</td>
                        <td class="px-6 py-3 text-slate-600 max-w-xs truncate">{{ $registration->event?->title ?? '–' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $registration->category?->name ?? '–' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $registration->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            Belum ada racepack yang diambil
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($registrations->hasPages())
    <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-slate-600">
            Menampilkan {{ $registrations->firstItem() }} hingga {{ $registrations->lastItem() }} dari {{ $registrations->total() }} data
        </div>
        <div class="flex gap-2">
            @if($registrations->onFirstPage())
                <button disabled class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">← Sebelumnya</button>
            @else
                <a href="{{ $registrations->previousPageUrl() }}" class="px-4 py-2 bg-white border border-gray-200 text-slate-700 hover:bg-gray-50 rounded-lg transition-colors">← Sebelumnya</a>
            @endif

            @if($registrations->hasMorePages())
                <a href="{{ $registrations->nextPageUrl() }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">Selanjutnya →</a>
            @else
                <button disabled class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg cursor-not-allowed">Selanjutnya →</button>
            @endif
        </div>
    </div>
@endif

{{-- QR Code Scanner Library --}}
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    let html5QrcodeScanner = null;
    let scannerActive = false;

    function toggleScanner() {
        const btn = document.getElementById('scannerToggleBtn');
        const container = document.getElementById('scannerContainer');
        
        if (!scannerActive) {
            container.classList.remove('hidden');
            btn.textContent = 'Tutup Kamera';
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-red-600', 'hover:bg-red-700');
            initScanner();
            scannerActive = true;
        } else {
            container.classList.add('hidden');
            btn.textContent = 'Buka Kamera';
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.remove('bg-red-600', 'hover:bg-red-700');
            stopScanner();
            scannerActive = false;
        }
    }

    function initScanner() {
        html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader",
            { 
                fps: 10,
                qrbox: {width: 250, height: 250},
                aspectRatio: 1
            },
            false
        );

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }

    function stopScanner() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(error => {
                console.log('Error stopping scanner:', error);
            });
        }
    }

    function onScanSuccess(decodedText, decodedResult) {
        // Extract invoice number from QR code (assuming it contains the invoice number)
        const invoice = decodedText.trim();
        
        // Check if it looks like an invoice number
        if (invoice.startsWith('INV-')) {
            submitInvoiceDirectly(invoice);
        }
    }

    function onScanFailure(error) {
        // Handle scan failure silently or log for debugging
        console.debug("QR Scan error: ", error);
    }

    function submitInvoice() {
        const invoice = document.getElementById('invoiceInput').value.trim();
        if (invoice) {
            submitInvoiceDirectly(invoice);
        } else {
            alert('Silakan masukkan nomor invoice');
        }
    }

    function submitInvoiceDirectly(invoice) {
        // Navigate to the racepack detail page for confirmation
        window.location.href = `{{ url('admin/racepack') }}/${invoice}`;
    }

    // Allow Enter key to submit
    document.getElementById('invoiceInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            submitInvoice();
        }
    });
</script>

@endsection
