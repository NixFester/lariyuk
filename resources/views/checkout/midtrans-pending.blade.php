@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-cyan-50 to-blue-100 px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        {{-- Auto-store payment in localStorage --}}
        <script>
          // Store this payment to localStorage list
          function getPaymentsList() {
            const stored = localStorage.getItem('lariyuk_payments');
            if (stored) {
              try {
                return JSON.parse(stored);
              } catch (e) {
                return [];
              }
            }
            return [];
          }

          function addPaymentToList(registration) {
            let payments = getPaymentsList();
            // Check if this exact invoice already exists
            const invoiceExists = payments.some(p => p.invoice === registration.invoice);
            if (!invoiceExists) {
              // Only add if this invoice doesn't already exist
              payments.unshift({
                id: registration.id,
                invoice: registration.invoice,
                addedAt: new Date().toISOString(),
                status: payment.status,
              });
              localStorage.setItem('lariyuk_payments', JSON.stringify(payments));
            }
          }

          // Add this registration to payments list
          addPaymentToList({
            id: {{ $registration->id }},
            invoice: '{{ $registration->invoice_number }}',
            status: '{{ $registration->payment_status }}'
          });
        </script>

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-cyan-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Midtrans Pembayaran</h1>
            <p class="text-gray-600 text-sm">Invoice: <span class="font-mono font-semibold">{{ $registration->invoice_number }}</span></p>
        </div>

        <!-- Payment Details -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-gray-200">
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Nama</span>
                    <span class="font-semibold text-gray-800">{{ $registration->nama_peserta }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Email</span>
                    <span class="font-semibold text-gray-800">{{ $registration->email }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Acara</span>
                    <span class="font-semibold text-gray-800">{{ $registration->event->title }}</span>
                </div>
                <div class="border-t border-gray-300 pt-3 flex justify-between">
                    <span class="text-gray-600 font-medium">Total Bayar</span>
                    <span class="text-lg font-bold text-cyan-600">Rp {{ number_format($registration->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Status Container -->
        <div id="statusContainer" class="mb-8">
            <div id="initialStatus" class="bg-cyan-50 border border-cyan-200 rounded-lg p-4 text-center">
                <div class="flex justify-center mb-3">
                    <svg class="animate-spin h-6 w-6 text-cyan-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-sm text-cyan-700 font-medium">Memuat layar pembayaran...</p>
            </div>

            <div id="successStatus" class="bg-green-50 border border-green-200 rounded-lg p-4 text-center hidden">
                <div class="flex justify-center mb-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm text-green-700 font-medium">Pembayaran berhasil!</p>
                <p class="text-xs text-green-600 mt-2">Tiket sedang disiapkan dan akan dikirim ke email Anda.</p>
            </div>

            <div id="errorStatus" class="bg-red-50 border border-red-200 rounded-lg p-4 text-center hidden">
                <div class="flex justify-center mb-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <p class="text-sm text-red-700 font-medium">Pembayaran dibatalkan atau gagal</p>
            </div>
        </div>

        <!-- Information -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-sm text-gray-700 space-y-2">
            <p class="font-semibold text-gray-800 flex items-center">
                <svg class="w-4 h-4 mr-2 text-cyan-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Instruksi:
            </p>
            <ul class="list-disc list-inside space-y-1 ml-6 text-xs">
                <li>Layar pembayaran akan terbuka secara otomatis</li>
                <li>Pilih metode pembayaran yang Anda inginkan</li>
                <li>Ikuti instruksi untuk menyelesaikan pembayaran</li>
                <li>Status akan diperbarui secara otomatis setelah pembayaran dikonfirmasi</li>
            </ul>
        </div>

        <!-- Cancel Button -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <form method="POST" action="{{ route('checkout.cancel', $registration->invoice_number) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pendaftaran?');">
                @csrf
                <button type="submit" class="w-full py-2 px-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium text-sm">
                    Batalkan Pendaftaran
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Load Midtrans Snap Library -->
<script src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $clientKey }}"></script>

<script>
    // Configuration
    const snapToken = '{{ $snapToken }}';
    const invoiceNumber = '{{ $registration->invoice_number }}';
    const checkStatusUrl = '{{ route('checkout.midtrans.check-status') }}';
    let popupAttempt = 0;
    const maxPopupAttempts = 3;
    const retryDelayMs = 3000; // 3 seconds between retries

    // Show Snap popup immediately
    function showMidtransPopup() {
      popupAttempt++;
      console.log(`Midtrans popup attempt ${popupAttempt}/${maxPopupAttempts}`);
      
      window.snap.pay(snapToken, {
        onSuccess: function(result) {
            console.log('Payment success:', result);
            updateStatus('success');
        },
        onPending: function(result) {
            console.log('Payment pending:', result);
            // Still wait for webhook confirmation
        },
        onError: function(result) {
            console.log('Payment error:', result);
            updateStatus('error');
        },
        onClose: function() {
            console.log('Payment popup closed');
            
            // Retry showing the popup if user closes it and we haven't exceeded max attempts
            if (popupAttempt < maxPopupAttempts) {
                console.log(`Popup closed. Retrying in ${retryDelayMs/1000} seconds...`);
                setTimeout(() => {
                    showMidtransPopup();
                }, retryDelayMs);
            } else {
                console.log('Max popup attempts reached. Starting polling...');
                // Start polling to check if payment was successful
                pollPaymentStatus();
            }
        }
      });
    }

    // Initial popup display
    showMidtransPopup();
    

    function updateStatus(status) {
        const statusContainer = document.getElementById('statusContainer');
        const initialStatus = document.getElementById('initialStatus');
        const successStatus = document.getElementById('successStatus');
        const errorStatus = document.getElementById('errorStatus');
        let payments = localStorage.getItem('lariyuk_payments');  

        initialStatus.classList.add('hidden');
        successStatus.classList.add('hidden');
        errorStatus.classList.add('hidden');

        if (status === 'success') {
            successStatus.classList.remove('hidden');
            payments = JSON.parse(payments || '[]');
            if (!payments.some(p => p.invoice === invoiceNumber)) {
              payments.push({
                id: {{ $registration->id }},
                invoice: '{{ $registration->invoice_number }}',
                addedAt: new Date().toISOString(),
                status: 'paid',
              });
              localStorage.setItem('lariyuk_payments', JSON.stringify(payments));
            }
            // Redirect to success page after 3 seconds
            setTimeout(() => {
                window.location.href = '{{ route('checkout.midtrans.success', $registration->invoice_number) }}';
            }, 3000);
        } else if (status === 'error') {
            errorStatus.classList.remove('hidden');
        }
    }

    function pollPaymentStatus() {
        // Poll every 2 seconds to check if payment was verified via webhook
        let pollCount = 0;
        const maxPoll = 150; // Poll for 5 minutes

        const pollInterval = setInterval(() => {
            if (pollCount >= maxPoll) {
                clearInterval(pollInterval);
                return;
            }

            fetch(checkStatusUrl + '?invoice=' + invoiceNumber)
                .then(response => response.json())
                .then(data => {
                    console.log('Payment status check:', data);
                    if (data.payment_status === 'paid') {
                        clearInterval(pollInterval);
                        updateStatus('success');
                    }
                })
                .catch(error => console.error('Status check error:', error));

            pollCount++;
        }, 2000);
    }
</script>
@endsection
