@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-12 px-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Midtrans Payment Debug</h1>
            
            <!-- Invoice Search -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Check Payment Status</h2>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="invoiceInput" 
                        placeholder="Enter invoice number (e.g., INV-20260402-ABC123)" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    >
                    <button 
                        onclick="checkStatus()" 
                        class="bg-cyan-600 hover:bg-cyan-700 text-white font-semibold py-2 px-6 rounded-lg transition"
                    >
                        Check Status
                    </button>
                </div>
            </div>

            <!-- Response Display -->
            <div id="responseContainer" class="hidden">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Response</h2>
                <div class="bg-gray-900 text-green-400 rounded-lg p-4 overflow-auto max-h-96 font-mono text-sm">
                    <pre id="responseOutput"></pre>
                </div>
            </div>

            <!-- Recent Registrations -->
            <div class="mt-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Recent Registrations</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 text-left">Invoice</th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registrations as $reg)
                            <tr class="border-b bg-white hover:bg-gray-50">
                                <td class="px-4 py-2 font-mono text-xs text-cyan-600">
                                    <a href="#" onclick="checkStatusWithInvoice('{{ $reg->invoice_number }}')">
                                        {{ $reg->invoice_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-2">{{ $reg->nama_peserta }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        @if($reg->payment_status === 'paid')
                                            bg-green-200 text-green-800
                                        @elseif($reg->payment_status === 'pending')
                                            bg-yellow-200 text-yellow-800
                                        @else
                                            bg-red-200 text-red-800
                                        @endif
                                    ">
                                        {{ ucfirst($reg->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <button 
                                        onclick="checkStatusWithInvoice('{{ $reg->invoice_number }}')"
                                        class="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-xs"
                                    >
                                        Check
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr class="border-b bg-white">
                                <td colspan="4" class="px-4 py-2 text-center text-gray-500">
                                    No registrations found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkStatusWithInvoice(invoice) {
    document.getElementById('invoiceInput').value = invoice;
    checkStatus();
}

function checkStatus() {
    const invoice = document.getElementById('invoiceInput').value;
    if (!invoice) {
        alert('Please enter an invoice number');
        return;
    }

    document.getElementById('responseContainer').classList.remove('hidden');
    document.getElementById('responseOutput').textContent = 'Loading...';

    // Try automatic check first
    console.log('Checking automatic status endpoint...');
    fetch('/checkout/midtrans/check-status?invoice=' + encodeURIComponent(invoice))
        .then(response => response.json())
        .then(data => {
            let output = 'AUTOMATIC STATUS CHECK:\n';
            output += JSON.stringify(data, null, 2);
            output += '\n\n';

            // Then try manual check
            console.log('Checking manual status endpoint...');
            return fetch('/checkout/midtrans/manual-check-status?invoice=' + encodeURIComponent(invoice))
                .then(response => response.json())
                .then(manualData => {
                    output += 'MANUAL STATUS CHECK:\n';
                    output += JSON.stringify(manualData, null, 2);
                    document.getElementById('responseOutput').textContent = output;
                });
        })
        .catch(error => {
            document.getElementById('responseOutput').textContent = 'Error: ' + error.message;
        });
}

// Auto-check on page load if there's a recent registration
document.addEventListener('DOMContentLoaded', function() {
    const firstInvoice = document.querySelector('a[onclick*="checkStatusWithInvoice"]');
    if (firstInvoice) {
        // Optionally auto-check the first registration
        // checkStatusWithInvoice(firstInvoice.textContent);
    }
});
</script>
@endsection
