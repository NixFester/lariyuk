<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Tiket – {{ $registration->event->title }}</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Helvetica Neue', Arial, sans-serif; background:#f1f5f7; color:#0f1720; }
    .wrapper { max-width:600px; margin:0 auto; padding:24px 16px; }

    /* Header */
    .header { background:linear-gradient(135deg,#00a35f 0%,#00d47b 100%); border-radius:16px 16px 0 0; padding:32px; text-align:center; }
    .header .logo { font-size:24px; font-weight:800; color:#fff; letter-spacing:-0.5px; }
    .header .logo span { color:#b3ffe0; }
    .header .tagline { color:#b3ffe0; font-size:13px; margin-top:4px; }

    /* Ticket body */
    .ticket { background:#fff; border-radius:0 0 16px 16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.08); }

    /* BIB strip */
    .bib-strip { background:#0f1720; padding:28px 32px; display:flex; align-items:center; justify-content:space-between; }
    .bib-label { color:#64748b; font-size:11px; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px; }
    .bib-number { color:#00d47b; font-size:52px; font-weight:900; line-height:1; letter-spacing:-2px; }
    .bib-name { color:#ffffff; font-size:18px; font-weight:700; margin-top:6px; }
    .bib-right { text-align:right; }
    .bib-cat { color:#00d47b; font-size:20px; font-weight:800; }
    .bib-event { color:#94a3b8; font-size:12px; margin-top:4px; max-width:160px; }

    /* Perforated divider */
    .perforation { background:#f1f5f7; display:flex; align-items:center; padding:0 20px; }
    .perf-circle-left  { width:20px; height:20px; border-radius:50%; background:#f1f5f7; margin-left:-30px; flex-shrink:0; }
    .perf-circle-right { width:20px; height:20px; border-radius:50%; background:#f1f5f7; margin-right:-30px; flex-shrink:0; }
    .perf-line { flex:1; border-top:2px dashed #e2e8f0; }

    /* Details grid */
    .details { padding:28px 32px; }
    .details-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#94a3b8; margin-bottom:16px; }
    .details-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .detail-item .label { font-size:11px; color:#94a3b8; margin-bottom:3px; }
    .detail-item .value { font-size:14px; font-weight:600; color:#0f1720; }
    .detail-item.full { grid-column:1 / -1; }

    /* Blood type badge */
    .badge-blood { display:inline-block; background:#fee2e2; color:#dc2626; font-weight:800; font-size:13px; padding:2px 10px; border-radius:20px; }
    .badge-eb    { display:inline-block; background:#fef9c3; color:#92400e; font-weight:700; font-size:11px; padding:2px 8px; border-radius:20px; }

    /* Total strip */
    .total-strip { background:#f8fafb; border-top:1px solid #e2e8f0; padding:20px 32px; display:flex; justify-content:space-between; align-items:center; }
    .total-label { font-size:13px; color:#64748b; }
    .total-amount { font-size:22px; font-weight:900; color:#00a35f; }

    /* QR placeholder */
    .qr-section { padding:20px 32px; text-align:center; border-top:1px solid #e2e8f0; }
    .qr-box { width:100px; height:100px; background:#f1f5f7; border:2px dashed #cbd5e1; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; color:#94a3b8; font-size:11px; margin-bottom:8px; }
    .qr-note { font-size:11px; color:#94a3b8; }
    .invoice-code { font-family:monospace; font-size:13px; font-weight:700; color:#0f1720; margin-top:4px; }

    /* Footer */
    .footer { text-align:center; padding:24px; color:#94a3b8; font-size:12px; line-height:1.8; }
    .footer a { color:#00a35f; text-decoration:none; }

    /* TEST BANNER */
    .test-banner { background:#ff6b35; color:#fff; text-align:center; font-size:12px; font-weight:700; padding:8px; border-radius:8px 8px 0 0; letter-spacing:.5px; }
  </style>
</head>
<body>
<div class="wrapper">

  {{-- HEADER --}}
  <div class="header">
    <div class="logo">Lari<span>Yuk</span></div>
    <div class="tagline">Platform Tiket Event Lari Indonesia</div>
  </div>

  <div class="ticket">

    {{-- BIB STRIP --}}
    <div class="bib-strip">
      <div>
        <div class="bib-label">Nickname / BIB</div>
        <div class="bib-number">{{ $registration->nickname }}</div>
        <div class="bib-name">{{ $registration->nama_peserta }}</div>
      </div>
      <div class="bib-right">
        <div class="bib-cat">{{ $registration->category->name }}</div>
        <div class="bib-event">{{ $registration->event->title }}</div>
        <div style="color:#64748b;font-size:12px;margin-top:8px;">
          {{ $registration->event->date->translatedFormat('d M Y') }}
        </div>
      </div>
    </div>

    {{-- PERFORATED DIVIDER --}}
    <div class="perforation">
      <div class="perf-circle-left"></div>
      <div class="perf-line"></div>
      <div class="perf-circle-right"></div>
    </div>

    {{-- DETAILS --}}
    <div class="details">
      <div class="details-title">Detail Peserta</div>
      <div class="details-grid">
        <div class="detail-item">
          <div class="label">Nama Lengkap</div>
          <div class="value">{{ $registration->nama_peserta }}</div>
        </div>
        <div class="detail-item">
          <div class="label">Golongan Darah</div>
          <div class="value"><span class="badge-blood">{{ $registration->golongan_darah }}</span></div>
        </div>
        <div class="detail-item">
          <div class="label">Jenis Kelamin</div>
          <div class="value">{{ $registration->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
        </div>
        <div class="detail-item">
          <div class="label">Ukuran Kaos</div>
          <div class="value">{{ $registration->ukuran_kaos }}</div>
        </div>
        <div class="detail-item">
          <div class="label">Lokasi</div>
          <div class="value">{{ $registration->event->venue }}</div>
        </div>
        <div class="detail-item">
          <div class="label">Waktu Start</div>
          <div class="value">{{ $registration->event->time }}</div>
        </div>
        <div class="detail-item">
          <div class="label">Kontak Darurat</div>
          <div class="value">{{ $registration->kontak_darurat_nama }} ({{ $registration->kontak_darurat_hp }})</div>
        </div>
        <div class="detail-item">
          <div class="label">Metode Bayar</div>
          <div class="value">
            {{ $registration->payment_method }}
            @if($registration->is_early_bird)
              &nbsp;<span class="badge-eb">Early Bird</span>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- TOTAL --}}
    <div class="total-strip">
      <span class="total-label">Total Dibayarkan</span>
      <span class="total-amount">Rp {{ number_format($registration->total, 0, ',', '.') }}</span>
    </div>

    {{-- QR PLACEHOLDER --}}
    <div class="qr-section">
      <div class="qr-box">QR Code</div>
      <div class="qr-note">Tunjukkan kode ini saat pengambilan race pack</div>
      <div class="invoice-code">{{ $registration->invoice_number }}</div>
    </div>

  </div>{{-- .ticket --}}

  {{-- FOOTER --}}
  <div class="footer">
    Simpan email ini sebagai bukti pendaftaran.<br>
    Pertanyaan? Hubungi support melalui WhatsApp: {{ config('app.whatsapp_number') }}<br><br>
    &copy; {{ date('Y') }} LariYuk &bull; Platform Tiket Event Lari Indonesia
  </div>

</div>
</body>
</html>
