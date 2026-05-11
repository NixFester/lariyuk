@extends('layouts.app')
@section('title', 'Daftar Peserta Terbayar - ' . $event->title)
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <a href="{{ route('events.show', $event->slug) }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-primary-600 mb-6">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Kembali ke event
  </a>

  <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="text-sm font-semibold text-primary-600">Event</p>
        <h1 class="font-display text-3xl font-bold text-slate-900">{{ $event->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">Temukan nama Anda di daftar peserta yang sudah terbayar, lalu klik tombol "Lihat Kartu" untuk membuka kartu pendaftaran Anda.</p>
      </div>
      <div class="rounded-3xl bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
        {{ number_format($registrations->count()) }} peserta terbayar
      </div>
    </div>

    @if($registrations->isEmpty())
      <div class="mt-8 rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
        <p class="text-lg font-semibold text-slate-900">Belum ada peserta yang sudah terbayar.</p>
        <p class="mt-2 text-sm text-slate-500">Silakan kembali lagi setelah pembayaran diverifikasi oleh admin.</p>
      </div>
    @else
      <div class="mt-8 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
          <thead class="bg-slate-50 text-slate-500 uppercase text-xs tracking-[0.16em]">
            <tr>
              <th class="px-4 py-3 text-right">Lihat kartu</th>
              <th class="px-4 py-3">Nama BIB</th>
              <th class="px-4 py-3">Kategori</th>
              <th class="px-4 py-3">Invoice</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($registrations as $registration)
              <tr class="hover:bg-slate-50">
                <td class="px-4 py-4 text-right">
                  <a href="{{ route('checkout.success', $registration->invoice_number) }}" class="inline-flex items-center justify-center rounded-full bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-slate-900/5 hover:bg-primary-700 transition">
                    Lihat Kartu
                  </a>
                </td>
                <td class="px-4 py-4 font-medium text-slate-900">{{ $registration->nickname }}</td>
                <td class="px-4 py-4 text-slate-600">{{ $registration->category?->name ?? 'Tidak tersedia' }}</td>
                <td class="px-4 py-4 font-mono text-xs text-slate-500">{{ $registration->invoice_number }}</td>
                
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-6 rounded-2xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-600">
        <p class="font-medium text-slate-800">Tips cepat:</p>
        <p>Gunakan fitur pencarian browser (Ctrl+F) untuk menemukan nama Anda lebih cepat jika daftar panjang.</p>
      </div>
    @endif
  </div>
</div>
@endsection
