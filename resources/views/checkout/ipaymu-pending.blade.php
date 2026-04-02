@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Menunggu Pembayaran</h1>
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
                    <span class="font-semibold text-gray-800">{{ $registration->event->name }}</span>
                </div>
                <div class="border-t border-gray-300 pt-3 flex justify-between">
                    <span class="text-gray-600 font-medium">Total Bayar</span>
                    <span class="text-lg font-bold text-blue-600">Rp {{ number_format($registration->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Timer -->
        <div id="timerContainer" class="mb-8">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-600 mb-2">Waktu tersisa untuk menyelesaikan pembayaran:</p>
                <div id="timer" class="text-3xl font-bold text-yellow-600">30:00</div>
            </div>
        </div>

        <!-- Status -->
        <div id="statusContainer" class="mb-8">
            <div id="pendingStatus" class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                <div class="flex justify-center mb-3">
                    <svg id="loadingSpinner" class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <p class="text-sm text-blue-700 font-medium">Memeriksa status pembayaran...</p>
            </div>

            <div id="successStatus" class="bg-green-50 border border-green-200 rounded-lg p-4 text-center hidden">
                <div class="flex justify-center mb-3">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm text-green-700 font-medium">Pembayaran berhasil diverifikasi!</p>
                <p class="text-xs text-green-600 mt-2">Tiket telah dikirim ke email Anda.</p>
            </div>

            <div id="expiredStatus" class="bg-red-50 border border-red-200 rounded-lg p-4 text-center hidden">
                <div class="flex justify-center mb-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm text-red-700 font-medium">Waktu pembayaran telah habis</p>
                <p class="text-xs text-red-600 mt-2">Silakan mendaftar ulang untuk melanjutkan.</p>
            </div>
        </div>

        <!-- Information -->
        <div id="infoBox" class="bg-gray-50 rounded-lg p-4 border border-gray-200 text-sm text-gray-700 space-y-2">
            <p class="font-semibold text-gray-800 flex items-center">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Instruksi:
            </p>
            <ul class="list-disc list-inside space-y-1 ml-6">
                <li>Halaman ini akan otomatis memeriksa status pembayaran setiap 5 detik</li>
                <li>Jangan menutup halaman ini sampai pembayaran dikonfirmasi</li>
                <li>Tiket akan langsung dikirim ke email Anda setelah pembayaran berhasil</li>
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

<!-- LocalStorage Integration for Payment Tracking -->
<script>
    // Save payment info to localStorage for future sync
    const paymentData = {
        invoice: '{{ $registration->invoice_number }}',
        email: '{{ $registration->email }}',
        amount: {{ $registration->total }},
        timestamp: new Date().toISOString(),
        device: {
            userAgent: navigator.userAgent,
            language: navigator.language,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        }
    };

    // Save to localStorage
    localStorage.setItem('lariyuk_payment_' + '{{ $registration->invoice_number }}', JSON.stringify(paymentData));

    // Also save to a pending payments array
    let pendingPayments = JSON.parse(localStorage.getItem('lariyuk_pending_payments') || '[]');
    if (!pendingPayments.find(p => p.invoice === paymentData.invoice)) {
        pendingPayments.push(paymentData);
        localStorage.setItem('lariyuk_pending_payments', JSON.stringify(pendingPayments));
    }

    console.log('Payment data saved to localStorage:', paymentData);
</script>

<!-- Payment Status Polling -->
<script>
    const invoice = '{{ $registration->invoice_number }}';
    const maxWaitTime = 30 * 60 * 1000; // 30 minutes
    let startTime = Date.now();
    let checkInterval;

    function formatTime(ms) {
        const totalSeconds = Math.floor(ms / 1000);
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    function updateTimer() {
        const elapsed = Date.now() - startTime;
        const remaining = maxWaitTime - elapsed;

        if (remaining <= 0) {
            clearInterval(checkInterval);
            clearInterval(timerInterval);
            document.getElementById('timerContainer').classList.add('hidden');
            document.getElementById('pendingStatus').classList.add('hidden');
            document.getElementById('expiredStatus').classList.remove('hidden');
            return;
        }

        document.getElementById('timer').textContent = formatTime(remaining);
    }

    function checkPaymentStatus() {
        fetch(`/checkout/ipaymu/check-status?invoice=${invoice}`)
            .then(response => response.json())
            .then(data => {
                if (data.isPaid) {
                    clearInterval(checkInterval);
                    clearInterval(timerInterval);
                    document.getElementById('pendingStatus').classList.add('hidden');
                    document.getElementById('successStatus').classList.remove('hidden');

                    // Update localStorage to mark as paid
                    const paymentData = JSON.parse(localStorage.getItem('lariyuk_payment_' + invoice));
                    if (paymentData) {
                        paymentData.status = 'paid';
                        paymentData.paidAt = new Date().toISOString();
                        localStorage.setItem('lariyuk_payment_' + invoice, JSON.stringify(paymentData));
                    }

                    // Redirect after 3 seconds
                    if (data.redirectTo) {
                        setTimeout(() => {
                            window.location.href = data.redirectTo;
                        }, 3000);
                    }
                } else {
                    console.log('Payment still pending...');
                }
            })
            .catch(error => {
                console.error('Error checking payment status:', error);
            });
    }

    // Check status every 5 seconds
    checkInterval = setInterval(checkPaymentStatus, 5000);

    // Update timer every second
    const timerInterval = setInterval(updateTimer, 1000);

    // Initial check and timer update
    checkPaymentStatus();
    updateTimer();
</script>
@endsection
