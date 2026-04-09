@extends('layouts.app')
@section('title', 'Registrasi Ulang Berhasil')
@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
  <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center">
    {{-- Success Icon --}}
    <div class="mb-6 flex justify-center">
      <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
      </div>
    </div>

    <h1 class="font-display text-3xl font-bold text-slate-900 mb-2">Registrasi Ulang Berhasil! 🎉</h1>
    
    <p class="text-lg text-slate-600 mb-6">
      Terima kasih telah menyelesaikan registrasi ulang Anda.
    </p>

    <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-8 text-left">
      <p class="text-sm text-slate-600 mb-3">
        <strong>Nomor Invoice:</strong><br>
        <span class="font-mono text-green-700 font-semibold">{{ $registration->invoice_number }}</span>
      </p>
      <p class="text-sm text-slate-600 mb-3">
        <strong>Email Terdaftar:</strong><br>
        <span class="text-slate-700">{{ $registration->email }}</span>
      </p>
      <p class="text-sm text-slate-600">
        <strong>Nama Peserta:</strong><br>
        <span class="text-slate-700">{{ $registration->nama_peserta }}</span>
      </p>
    </div>

    {{-- Status Notice --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
      <div class="flex gap-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-left">
          <p class="text-sm text-blue-700 font-medium mb-1">Tim Admin Sedang Memproses</p>
          <p class="text-sm text-blue-600">
            Data registrasi ulang Anda telah diterima. Tim admin kami akan segera memverifikasi dan mengirimkan tiket digital ke email Anda.
          </p>
        </div>
      </div>
    </div>

    {{-- What's Next --}}
    <div class="mb-8">
      <h2 class="font-semibold text-slate-900 mb-4">Langkah Selanjutnya</h2>
      <div class="text-left space-y-3">
        <div class="flex gap-3">
          <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-sm font-bold text-green-700">✓</div>
          <div>
            <p class="text-sm font-medium text-slate-900">Registrasi Ulang Selesai</p>
            <p class="text-xs text-slate-500">Data Anda telah disimpan dengan aman</p>
          </div>
        </div>
        <div class="flex gap-3">
          <div class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-sm font-bold text-slate-600">2</div>
          <div>
            <p class="text-sm font-medium text-slate-900">Verifikasi Admin</p>
            <p class="text-xs text-slate-500">Tunggu konfirmasi dari tim admin (biasanya dalam 24 jam)</p>
          </div>
        </div>
        <div class="flex gap-3">
          <div class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-sm font-bold text-slate-600">3</div>
          <div>
            <p class="text-sm font-medium text-slate-900">Terima Tiket Digital</p>
            <p class="text-xs text-slate-500">Tiket akan dikirim ke email Anda yang terdaftar</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Additional Info --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8 text-left">
      <p class="text-sm text-amber-800">
        <strong>📢 Penting:</strong> Jika Anda tidak menerima tiket dalam 24 jam, silakan hubungi tim support kami melalui WhatsApp atau email. Jangan melakukan registrasi ulang lagi, karena link Anda hanya berlaku sekali dan sudah terpakai.
      </p>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('home') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-900 font-semibold rounded-xl transition-colors">
        Kembali ke Beranda
      </a>
      <a href="{{ route('events.index') }}" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors">
        Lihat Event Lainnya
      </a>
    </div>
  </div>
</div>
@endsection
