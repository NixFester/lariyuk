@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-cyan-50 to-blue-100 px-4 py-12">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-cyan-100 rounded-full flex items-center justify-center mb-4">
                <svg class="animate-spin h-8 w-8 text-cyan-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Memproses Pembayaran</h1>
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

        <!-- Status Message -->
        <div class="bg-cyan-50 border border-cyan-200 rounded-lg p-6 mb-8 text-center">
            <p class="text-gray-700 font-medium mb-4">Kami sedang memproses pembayaran Anda...</p>
            <p class="text-sm text-gray-600 mb-4">Halaman ini akan otomatis diperbarui setiap beberapa detik.</p>
            <p class="text-xs text-gray-500">Status Saat Ini: <span id="currentStatus">Diproses</span></p>
        </div>

        <!-- Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700 space-y-2">
            <p class="font-semibold flex items-center">
                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Catatan Penting:
            </p>
            <ul class="list-disc list-inside space-y-1 ml-2 text-xs">
                <li>Jangan menutup atau refresh halaman ini</li>
                <li>Proses verifikasi biasanya memerlukan waktu beberapa detik</li>
                <li>Tiket akan dikirim ke email Anda setelah pembayaran dikonfirmasi</li>
            </ul>
        </div>

        <!-- Manual Check Button -->
        <div class="mt-6 flex gap-2">
            <button id="manualCheckBtn" class="flex-1 bg-cyan-600 hover:bg-cyan-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                Cek Status Pembayaran
            </button>
        </div>
    </div>
</div>

<script>
    const invoiceNumber = '{{ $registration->invoice_number }}';
    const checkStatusUrl = '{{ route('checkout.midtrans.check-status') }}';
    const manualCheckStatusUrl = '{{ route('checkout.midtrans.manual-check-status') }}';
    const successUrl = '{{ route('checkout.midtrans.success', $registration->invoice_number) }}';
    
    let pollCount = 0;
    const maxPoll = 150; // Poll for 5 minutes (150 * 2 seconds)
    let isPolling = false;

    function checkPaymentStatus() {
        if (isPolling) return; // Prevent duplicate requests
        isPolling = true;

        fetch(checkStatusUrl + '?invoice=' + invoiceNumber)
            .then(response => response.json())
            .then(data => {
                console.log('Auto payment status check:', data);
                document.getElementById('currentStatus').textContent = data.payment_status || data.transaction_status || 'Diproses';
                
                if (data.payment_status === 'paid') {
                    console.log('Payment confirmed! Redirecting to success page...');
                    
                    window.location.href = successUrl;
                }
            })
            .catch(error => {
                console.error('Error checking status:', error);
                document.getElementById('currentStatus').textContent = 'Error checking status';
            })
            .finally(() => {
                isPolling = false;
            });
    }

    // Manual check button handler
    document.getElementById('manualCheckBtn').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        fetch(manualCheckStatusUrl + '?invoice=' + invoiceNumber)
            .then(response => response.json())
            .then(data => {
                console.log('Manual status check response:', data);
                
                if (data.payment_status === 'paid' || (data.success && data.payment_status === 'paid')) {
                    alert('Pembayaran sudah dikonfirmasi! Anda akan dialihkan ke halaman sukses.');
                    window.location.href = successUrl;
                } else if (data.success === false) {
                    alert('Status pembayaran: ' + (data.payment_status || data.message || 'Belum dikonfirmasi'));
                } else {
                    alert(data.message || 'Cek status berhasil. Periksa kembali dalam beberapa saat.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengecek status. Error: ' + error.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Cek Status Pembayaran';
            });
    });

    // Check payment status every 2 seconds (automatic)
    const statusInterval = setInterval(() => {
        if (pollCount >= maxPoll) {
            clearInterval(statusInterval);
            console.log('Maximum polling attempts reached');
            document.getElementById('currentStatus').textContent = 'Polling timeout - gunakan tombol manual check';
            return;
        }
        
        checkPaymentStatus();
        pollCount++;
    }, 2000);

    // Initial check immediately
    checkPaymentStatus();
</script>
@endsection
