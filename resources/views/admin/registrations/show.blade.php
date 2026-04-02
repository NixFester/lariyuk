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

  <div class="mt-6">
    <form action="{{ route('admin.registrations.destroy', $registration->id) }}" method="POST" onsubmit="return confirm('Hapus data pendaftar ini permanen?')">
      @csrf @method('DELETE')
      <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">Hapus Data Pendaftar</button>
    </form>
  </div>
</div>
@endsection
