@extends('layouts.admin')
@section('title','Detail Pendaftar')
@section('admin-content')
<div class="max-w-3xl">
  <a href="{{ route('admin.registrations.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-green-600 mb-6">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Kembali
  </a>

  <div class="grid sm:grid-cols-2 gap-6">
    {{-- BIB Card --}}
    <div class="sm:col-span-2 bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-green-200 text-sm">Nickname / BIB</p>
          <p class="font-display text-5xl font-bold mt-1">{{ $registration->nickname }}</p>
          <p class="text-green-100 text-lg mt-2 font-semibold">{{ $registration->nama_peserta }}</p>
        </div>
        <div class="text-right">
          <p class="text-green-200 text-xs">Kategori</p>
          <p class="font-bold text-xl">{{ $registration->category?->name }}</p>
          <p class="text-green-200 text-xs mt-2">Invoice</p>
          <p class="font-mono text-sm">{{ $registration->invoice_number }}</p>
        </div>
      </div>
    </div>

    {{-- Identitas --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
      <h3 class="font-display font-bold text-slate-800 mb-4">Data Identitas</h3>
      <dl class="space-y-3 text-sm">
        <div class="flex justify-between"><dt class="text-slate-500">No. KTP</dt><dd class="font-mono font-medium">{{ $registration->no_ktp }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Tanggal Lahir</dt><dd>{{ $registration->tanggal_lahir?->format('d M Y') }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Jenis Kelamin</dt><dd>{{ $registration->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">Golongan Darah</dt>
          <dd><span class="px-2 py-0.5 bg-red-100 text-red-700 rounded font-bold">{{ $registration->golongan_darah }}</span></dd>
        </div>
        <div class="flex justify-between"><dt class="text-slate-500">Ukuran Kaos</dt><dd class="font-bold">{{ $registration->ukuran_kaos }}</dd></div>
      </dl>
    </div>

    {{-- Kontak --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
      <h3 class="font-display font-bold text-slate-800 mb-4">Kontak</h3>
      <dl class="space-y-3 text-sm">
        <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $registration->email }}</dd></div>
        <div class="flex justify-between"><dt class="text-slate-500">No. HP</dt><dd>{{ $registration->phone }}</dd></div>
        <div class="pt-3 border-t border-gray-100">
          <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Kontak Darurat</p>
          <div class="flex justify-between"><dt class="text-slate-500">Nama</dt><dd>{{ $registration->kontak_darurat_nama }}</dd></div>
          <div class="flex justify-between mt-1"><dt class="text-slate-500">HP</dt><dd>{{ $registration->kontak_darurat_hp }}</dd></div>
        </div>
      </dl>
    </div>

    {{-- Event & Payment --}}
    <div class="sm:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
      <h3 class="font-display font-bold text-slate-800 mb-4">Event & Pembayaran</h3>
      <div class="grid sm:grid-cols-2 gap-4 text-sm">
        <dl class="space-y-3">
          <div class="flex justify-between"><dt class="text-slate-500">Event</dt><dd class="font-medium text-right max-w-48">{{ $registration->event?->title }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Kategori</dt><dd class="font-medium">{{ $registration->category?->name }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Tanggal Event</dt><dd>{{ $registration->event?->date->format('d M Y') }}</dd></div>
        </dl>
        <dl class="space-y-3">
          <div class="flex justify-between"><dt class="text-slate-500">Harga Tiket</dt><dd>Rp {{ number_format($registration->subtotal,0,',','.') }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Biaya Admin</dt><dd>Rp {{ number_format($registration->admin_fee,0,',','.') }}</dd></div>
          <div class="flex justify-between font-bold border-t border-gray-100 pt-3"><dt>Total Bayar</dt><dd class="text-green-600">Rp {{ number_format($registration->total,0,',','.') }}</dd></div>
          <div class="flex justify-between text-xs"><dt class="text-slate-500">Early Bird</dt><dd>{{ $registration->is_early_bird ? 'Ya' : 'Tidak' }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Metode</dt><dd>{{ $registration->payment_method ?? '–' }}</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Status</dt>
            <dd><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $registration->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($registration->payment_status) }}</span></dd>
          </div>
        </dl>
      </div>
    </div>
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
  <div class="flex flex-col sm:flex-row gap-3 mb-6">
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
  </div>

  <div class="mt-6">
    <form action="{{ route('admin.registrations.destroy', $registration->id) }}" method="POST" onsubmit="return confirm('Hapus data pendaftar ini permanen?')">
      @csrf @method('DELETE')
      <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">Hapus Data Pendaftar</button>
    </form>
  </div>
</div>
@endsection

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
    .max-w-3xl > * { display: none !important; }
    
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
