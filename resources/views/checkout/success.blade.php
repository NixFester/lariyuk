    @extends('layouts.app')
@section('title','Pendaftaran Berhasil!')
@section('content')

{{-- QRIS Payment Modal --}}
@if($registration->payment_status === 'pending' && !$registration->whatsapp_confirmed_at)
<div id="qrisModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl width-full max-w-md p-8 text-center">
    <h2 class="text-2xl font-bold text-slate-900 mb-2">Lakukan Pembayaran</h2>
    <p class="text-slate-500 text-sm mb-6">Scan QRIS di bawah menggunakan aplikasi pembayaran mobile</p>

    {{-- QRIS Code Display --}}
    <div class="bg-slate-50 rounded-xl p-6 mb-6 flex justify-center">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode('QRIS-' . $registration->invoice_number) }}" alt="QRIS Code" class="w-48 h-48">
    </div>

    {{-- Timer --}}
    <div class="mb-4 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-lg">
      <p class="text-xs text-yellow-700 font-semibold">Waktu tersisa:</p>
      <p class="text-2xl font-black text-yellow-600"><span id="qrisTimer">10:00</span></p>
      <p class="text-xs text-yellow-600 mt-1">Halaman akan refresh otomatis setelah waktu habis</p>
    </div>

    {{-- WhatsApp Confirmation --}}
    <form action="{{ route('checkout.confirm-payment', $registration->invoice_number) }}" method="POST" class="mb-3">
      @csrf
      <button type="submit" class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl transition-colors">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.946 1.23l-.356.214-3.71-.973.992 3.63-.235.364a9.864 9.864 0 001.516 5.163c.732 1.23 1.91 2.44 3.315 3.204 1.405.765 2.93 1.178 4.455 1.178h.005c4.937 0 8.945-4.027 8.945-8.973 0-2.396-.928-4.665-2.605-6.364-1.677-1.699-3.904-2.634-6.264-2.634z"/>
        </svg>
        Sudah Bayar? Konfirmasi via WhatsApp
      </button>
    </form>

    <p class="text-xs text-slate-500">Dengan mengklik tombol di atas, Anda mengkonfirmasi bahwa pembayaran sudah dilakukan. Admin akan segera memverifikasi pembayaran Anda.</p>
  </div>
</div>

<script>
  (function() {
    const QRIS_DURATION = 10 * 60; // 10 minutes in seconds
    let timeRemaining = QRIS_DURATION;

    function updateTimer() {
      const minutes = Math.floor(timeRemaining / 60);
      const seconds = timeRemaining % 60;
      document.getElementById('qrisTimer').textContent = 
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

      if (timeRemaining <= 0) {
        // Force page refresh after time expires
        location.reload();
      } else {
        timeRemaining--;
        setTimeout(updateTimer, 1000);
      }
    }

    updateTimer();
  })();
</script>
@endif

<div class="max-w-2xl mx-auto px-4 py-10">

  {{-- Success/Confirmation Messages --}}
  @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div>
          <p class="font-semibold text-green-700">{{ session('success') }}</p>
          <p class="text-sm text-green-600 mt-1">Pendaftaran Anda akan diverifikasi oleh admin dalam waktu 1x24 jam.</p>
        </div>
      </div>
    </div>
  @endif

  @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
      <p class="text-red-700 font-semibold">{{ session('error') }}</p>
    </div>
  @endif

  {{-- Success header --}}
  <div class="text-center mb-8">
    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
      <svg class="w-9 h-9 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
      </svg>
    </div>
    <h1 class="font-display text-3xl font-bold text-slate-900 mb-1">Pendaftaran Berhasil! 🎉</h1>
    <p class="text-slate-500 text-sm">Tiket dikirim ke <span class="font-semibold text-slate-700">{{ $registration->email }}</span></p>
  </div>

  {{-- ═══════════════════════════════════════════════════
       VISUAL TICKET — mirror of the email template
  ═══════════════════════════════════════════════════ --}}
  <div id="ticket" class="rounded-2xl overflow-hidden shadow-2xl mb-8" style="font-family:'Plus Jakarta Sans',sans-serif">

    {{-- Header bar --}}
    <div class="flex items-center justify-between px-7 py-5" style="background:linear-gradient(135deg,#00a35f 0%,#00d47b 100%)">
      <div>
        <div class="font-display font-black text-xl text-white tracking-tight">Lari<span style="color:#b3ffe0">Yuk</span></div>
        <div class="text-xs mt-0.5" style="color:#b3ffe0">Platform Tiket Event Lari Indonesia</div>
      </div>
      <svg class="w-8 h-8 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
    </div>

    {{-- BIB strip --}}
    <div class="flex items-center justify-between px-7 py-7 bg-slate-900">
      <div>
        <p class="text-xs font-semibold tracking-widest uppercase mb-2" style="color:#64748b">Nickname / BIB</p>
        <p class="font-black leading-none" style="font-size:56px;color:#00d47b;letter-spacing:-2px">
          {{ $registration->nickname }}
        </p>
        <p class="text-white font-semibold mt-2 text-base">{{ $registration->nama_peserta }}</p>
      </div>
      <div class="text-right">
        <p class="font-black text-2xl" style="color:#00d47b">{{ $registration->category->name }}</p>
        <p class="text-xs mt-1 max-w-36" style="color:#94a3b8;line-height:1.4">{{ $registration->event->title }}</p>
        <p class="text-xs mt-2" style="color:#64748b">{{ $registration->event->date->translatedFormat('d M Y') }}</p>
        <p class="text-xs" style="color:#64748b">{{ $registration->event->time }}</p>
      </div>
    </div>

    {{-- Perforation divider --}}
    <div class="relative flex items-center bg-white" style="height:28px">
      <div class="absolute -left-3 w-6 h-6 rounded-full bg-gray-100"></div>
      <div class="w-full border-t-2 border-dashed border-gray-200 mx-4"></div>
      <div class="absolute -right-3 w-6 h-6 rounded-full bg-gray-100"></div>
    </div>

    {{-- Details --}}
    <div class="bg-white px-7 py-5">
      <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Detail Peserta</p>
      <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
        <div>
          <p class="text-xs text-slate-400 mb-0.5">Nama Lengkap</p>
          <p class="font-semibold text-slate-800">{{ $registration->nama_peserta }}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 mb-0.5">Golongan Darah</p>
          <span class="inline-block px-3 py-0.5 bg-red-100 text-red-700 font-bold rounded-full text-sm">
            {{ $registration->golongan_darah }}
          </span>
        </div>
        <div>
          <p class="text-xs text-slate-400 mb-0.5">Jenis Kelamin</p>
          <p class="font-semibold text-slate-800">{{ $registration->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 mb-0.5">Ukuran Kaos</p>
          <p class="font-bold text-slate-800 text-base">{{ $registration->ukuran_kaos }}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 mb-0.5">Lokasi</p>
          <p class="font-semibold text-slate-800">{{ $registration->event->venue }}</p>
        </div>
        <div>
          <p class="text-xs text-slate-400 mb-0.5">Metode Bayar</p>
          <p class="font-semibold text-slate-800">
            {{ $registration->payment_method }}
            @if($registration->is_early_bird)
              <span class="ml-1 px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">Early Bird</span>
            @endif
          </p>
        </div>
        <div class="col-span-2 pt-3 border-t border-gray-100">
          <p class="text-xs text-slate-400 mb-0.5">Kontak Darurat</p>
          <p class="font-semibold text-slate-800">{{ $registration->kontak_darurat_nama }} — {{ $registration->kontak_darurat_hp }}</p>
        </div>
      </div>
    </div>

    {{-- Total --}}
    <div class="flex items-center justify-between px-7 py-4 bg-gray-50 border-t border-gray-200">
      <span class="text-sm text-slate-500 font-medium">Total Dibayarkan</span>
      <span class="font-display font-black text-2xl text-green-600">Rp {{ number_format($registration->total,0,',','.') }}</span>
    </div>

    {{-- QR / Invoice section --}}
    <div class="bg-white border-t border-dashed border-gray-200 px-7 py-5 flex items-center gap-6">
      {{-- QR Code --}}
      <div class="w-20 h-20 flex-shrink-0 rounded-lg flex items-center justify-center bg-white">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($registration->invoice_number) }}" alt="QR Code" class="w-20 h-20 rounded">
      </div>
      <div>
        <p class="text-xs text-slate-400 mb-1">Tunjukkan kode ini saat pengambilan race pack</p>
        <p class="font-mono font-bold text-slate-800 text-sm">{{ $registration->invoice_number }}</p>
        @if($registration->ticket_email_sent)
          <div class="flex items-center gap-1 mt-2">
            <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="text-xs text-green-600 font-medium">Tiket terkirim ke email</span>
          </div>
        @else
          <div class="flex items-center gap-1 mt-2">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span class="text-xs text-slate-500">Email dikirim ke {{ $registration->email }}</span>
          </div>
        @endif
      </div>
    </div>

  </div>
  {{-- #ticket --}}

  {{-- Actions --}}
  <div class="flex flex-col sm:flex-row gap-3">
    <button onclick="downloadTicketPDF()"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      Cetak Tiket
    </button>
    <form action="{{ route('checkout.resend-ticket', $registration->invoice_number) }}" method="POST" class="flex-1">
      @csrf
      <button type="submit" onclick="sendTicketEmail(event)"
              class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        Kirim ke Email
      </button>
    </form>
    <a href="{{ route('home') }}" class="flex-1 flex items-center justify-center px-5 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-slate-700 font-semibold rounded-xl transition-colors">
      Kembali ke Beranda
    </a>
    <a href="{{ route('events.index') }}" class="flex-1 flex items-center justify-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors">
      Lihat Event Lain
    </a>
  </div>

</div>

@push('styles')
<style>
  @media print {
    @page {
      size: 85.6mm 53.98mm;
      margin: 0;
      padding: 0;
    }
    
    * {
      margin: 0;
      padding: 0;
    }
    
    html, body {
      width: 85.6mm;
      height: 53.98mm;
      margin: 0;
      padding: 0;
      background: white;
    }
    
    /* Hide layout wrapper and all its siblings */
    nav { display: none !important; }
    footer { display: none !important; }
    .max-w-2xl > * { display: none !important; }
    
    /* Hide success messages */
    .bg-green-50, .bg-red-50 { display: none !important; }
    
    /* Hide header section */
    .text-center.mb-8 { display: none !important; }
    
    /* Hide action buttons */
    .flex.flex-col.sm\:flex-row { display: none !important; }
    
    /* Show ONLY the ticket */
    #ticket {
      display: block !important;
      width: 85.6mm;
      height: 53.98mm;
      margin: 0 !important;
      padding: 0 !important;
      overflow: hidden;
      border-radius: 0 !important;
      box-shadow: none !important;
      color-adjust: exact;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    
    /* Ensure ticket children display properly */
    #ticket div {
      page-break-inside: avoid;
    }
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/3.0.3/jspdf.umd.min.js"></script>
<script>
  function sendTicketEmail(event) {
    event.preventDefault();
    const form = event.target.closest('form');
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Mengirim...';
    
    fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('Tiket berhasil dikirim ke email Anda!');
        button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> Kirim ke Email';
        button.disabled = false;
      } else {
        throw new Error(data.message || 'Gagal mengirim tiket');
      }
    })
    .catch(error => {
      alert('Gagal mengirim tiket: ' + error.message);
      button.innerHTML = originalText;
      button.disabled = false;
    });
  }

  async function downloadTicketPDF() {
    const element = document.getElementById('ticket');
    const invoiceNumber = '{{ $registration->invoice_number }}';
    
    try {
      // Capture the ticket element as canvas
      const canvas = await html2canvas(element, {
        scale: 3,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff',
        logging: false
      });
      
      // Create PDF with exact ticket dimensions (640x761 px)
      // Convert 640x761 px to mm (at 96 DPI)
      const pdfWidth = 169.33;  // 640px
      const pdfHeight = 201.35; // 761px
      
      const jsPDFLib = window.jspdf;
      const jsPDF = jsPDFLib.jsPDF;
      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: [pdfWidth, pdfHeight]
      });
      
      // Add image to PDF without margins
      const imgData = canvas.toDataURL('image/jpeg', 0.95);
      pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
      
      // Download the PDF
      pdf.save(`tiket-${invoiceNumber}.pdf`);
    } catch (error) {
      console.error('Error generating PDF:', error);
      alert('Gagal mengunduh tiket PDF. Silakan coba lagi.');
    }
  }
</script>
@endpush
@endsection
