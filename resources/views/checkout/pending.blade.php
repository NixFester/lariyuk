@extends('layouts.app')
@section('title','Simpan Pembayaran Anda!')
@section('content')

<div class="max-w-4xl mx-auto px-4 py-10">

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
      invoice: '{{ $registration->invoice_number }}'
    });
  </script>

  {{-- Header --}}
  <div class="text-center mb-8">
    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
      <svg class="w-9 h-9 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <h1 class="font-display text-3xl font-bold text-slate-900 mb-2">Selesaikan Pembayaran</h1>
    <p class="text-slate-500 text-sm">Invoice: <span class="font-mono font-semibold text-slate-700">{{ $registration->invoice_number }}</span></p>
  </div>

  {{-- Status Messages --}}
  @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
      <p class="text-green-700 font-semibold">{{ session('success') }}</p>
    </div>
  @endif

  @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-700 font-semibold">{{ session('error') }}</p>
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Registration Summary --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="font-semibold text-slate-900 mb-4 text-lg">Ringkasan Pendaftaran</h3>
        
        <div class="space-y-3 mb-6 pb-6 border-b border-gray-200">
          <div>
            <p class="text-xs text-slate-400 mb-0.5">Nama Peserta</p>
            <p class="font-semibold text-slate-800">{{ $registration->nama_peserta }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-400 mb-0.5">Event</p>
            <p class="text-sm text-slate-700">{{ $registration->event->title }}</p>
          </div>
          <div>
            <p class="text-xs text-slate-400 mb-0.5">Kategori</p>
            <p class="font-semibold text-slate-800">{{ $registration->category->name }}</p>
          </div>
        </div>

        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-slate-600">Harga:</span>
            <span class="font-semibold">Rp {{ number_format($registration->subtotal, 0, ',', '.') }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-slate-600">Biaya Admin:</span>
            <span class="font-semibold">Rp {{ number_format($registration->admin_fee, 0, ',', '.') }}</span>
          </div>
          <div class="flex justify-between text-sm pt-2 border-t border-gray-200 font-bold text-base">
            <span>Total:</span>
            <span class="text-green-600">Rp {{ number_format($registration->total, 0, ',', '.') }}</span>
          </div>
        </div>

        {{-- QR Code --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
          <p class="text-xs text-slate-400 mb-3 font-semibold">QR Code Invoice</p>
          <div class="flex justify-center p-2 bg-gray-50 rounded-lg">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($registration->invoice_number) }}" alt="QR Code" class="w-24 h-24">
          </div>
        </div>
      </div>
    </div>

    {{-- Payment Methods Grid --}}
    <div class="lg:col-span-2">
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="font-semibold text-slate-900 mb-4 text-lg">Pilih Metode Pembayaran</h3>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
          @forelse($paymentMethods as $method)
            <button type="button"
                    class="flex flex-col items-center justify-center gap-2 p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all cursor-pointer group"
                    onclick="openPaymentModal({{ $method->id }})">
              @if($method->icon)
                <img src="{{ asset('storage/' . $method->icon) }}" alt="{{ $method->name }}" class="w-10 h-10 object-contain">
              @else
                <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center text-xs font-bold text-slate-600">
                  {{ substr($method->name, 0, 2) }}
                </div>
              @endif
              <p class="text-xs sm:text-sm font-semibold text-slate-700 text-center">{{ $method->name }}</p>
            </button>
          @empty
            <p class="text-slate-500 text-center py-8 col-span-full">Tidak ada metode pembayaran yang tersedia.</p>
          @endforelse
        </div>

        {{-- Confirmation Buttons --}}
        <div class="space-y-3 pt-6 border-t border-gray-200">
          @php
            $waNumber = env('WHATSAAP_NUMBER');
            $verifyLink = route('checkout.verify', $registration->invoice_number, true); // Absolute URL
            $waMessage = "Halo, saya telah menyelesaikan pembayaran untuk invoice: " . $registration->invoice_number . " Nama: " . $registration->nama_peserta . " Silakan klik link berikut untuk memverifikasi pembayaran (login terlebih dahulu jika belum): " . $verifyLink;
            $waLink = "https://wa.me/{$waNumber}?text=" . urlencode($waMessage);
          @endphp
          
          <form action="{{ route('checkout.confirm-payment', $registration->invoice_number) }}" method="POST" onsubmit="window.open('{{ $waLink }}', '_blank');">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition-colors">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.946 1.23l-.356.214-3.71-.973.992 3.63-.235.364a9.864 9.864 0 001.516 5.163c.732 1.23 1.91 2.44 3.315 3.204 1.405.765 2.93 1.178 4.455 1.178h.005c4.937 0 8.945-4.027 8.945-8.973 0-2.396-.928-4.665-2.605-6.364-1.677-1.699-3.904-2.634-6.264-2.634z"/>
              </svg>
              Sudah Bayar? Konfirmasi via WhatsApp
            </button>
          </form>

          <form action="{{ route('checkout.cancel', $registration->invoice_number) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pendaftaran ini?')">
            @csrf
            @method('POST')
            <button type="submit" class="w-full px-5 py-3 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Batalkan Pendaftaran
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Payment Method Modals --}}
@foreach($paymentMethods as $method)
<div id="paymentModal{{ $method->id }}" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl max-w-md p-8 text-center">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-2xl font-bold text-slate-900">{{ $method->name }}</h2>
      <button onclick="closePaymentModal({{ $method->id }})" class="text-slate-400 hover:text-slate-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    {{-- Payment Details --}}
    @if($method->name === 'QRIS')
      <div class="bg-slate-50 rounded-xl p-6 mb-6 flex justify-center">
        <img src="{{ asset('qris.jpeg') }}" alt="QRIS Code" class="w-56 h-56">
      </div>
    @else
    <p class="text-slate-500 text-sm mb-6">{{ $method->description }}</p>
      <div class="mb-6 p-4 bg-gray-100 rounded-xl">
        <p class="text-xs text-slate-500 mb-2 font-semibold">INSTRUKSI PEMBAYARAN:</p>
        <p class="text-sm text-slate-700 font-mono">{{ $method->placeholder }}</p>
      </div>

      @if($method->display_number)
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <p class="text-xs font-semibold text-blue-900 mb-1">NOMOR/AKUN:</p>
          <p class="text-lg font-bold text-blue-600 font-mono">{{ $method->display_number }}</p>
        </div>
      @endif
    @endif

    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
      <p class="text-xs text-yellow-700">
        <strong>Invoice:</strong><br>
        <span class="font-mono text-sm font-bold">{{ $registration->invoice_number }}</span>
      </p>
    </div>

    <p class="text-xs text-slate-600 mb-4">
      Setelah melakukan pembayaran, klik tombol "Sudah Bayar? Konfirmasi via WhatsApp" untuk mengkonfirmasi pembayaran Anda.
    </p>

    <button onclick="closePaymentModal({{ $method->id }})" class="w-full px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition-colors">
      Tutup
    </button>
  </div>
</div>
@endforeach

<script>
function openPaymentModal(methodId) {
  document.getElementById('paymentModal' + methodId).classList.remove('hidden');
}

function closePaymentModal(methodId) {
  document.getElementById('paymentModal' + methodId).classList.add('hidden');
}
</script>

@endsection
