@extends('layouts.admin')
@section('title','Dashboard')
@section('admin-content')

{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
  @foreach([
    ['label'=>'Total Event','value'=>$stats['total_events'],'color'=>'blue'],
    ['label'=>'Event Aktif','value'=>$stats['active_events'],'color'=>'green'],
    ['label'=>'Total Pendaftar','value'=>number_format($stats['total_registrations']),'color'=>'purple'],
    ['label'=>'Pembayaran Lunas','value'=>number_format($stats['paid_registrations']),'color'=>'emerald'],
    ['label'=>'Total Pendapatan','value'=>'Rp '.number_format($stats['total_revenue'],0,',','.'),'color'=>'orange'],
  ] as $s)
    <div class="bg-white rounded-2xl border border-gray-200 p-5">
      <p class="text-sm text-slate-500 mb-1">{{ $s['label'] }}</p>
      <p class="text-2xl font-display font-bold text-slate-900">{{ $s['value'] }}</p>
    </div>
  @endforeach
</div>

{{-- Quick Actions --}}
<div class="flex gap-3 mb-8">
  <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
    + Tambah Event
  </a>
  <a href="{{ route('admin.registrations.export') }}" class="px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-slate-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
    Export CSV
  </a>
</div>

{{-- Recent Registrations --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
  <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
    <h2 class="font-display font-bold text-slate-800">Pendaftar Terbaru</h2>
    <a href="{{ route('admin.registrations.index') }}" class="text-sm text-green-600 hover:text-green-700">Lihat Semua</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-gray-50 text-left">
        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">BIB</th>
        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Nama</th>
        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Event</th>
        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Kategori</th>
        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Total</th>
        <th class="px-6 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($recentRegistrations as $r)
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-3 font-mono font-bold text-green-600">{{ $r->bib_number ?? '–' }}</td>
            <td class="px-6 py-3 font-medium text-slate-900">{{ $r->nama_peserta }}</td>
            <td class="px-6 py-3 text-slate-600 max-w-xs truncate">{{ $r->event?->title }}</td>
            <td class="px-6 py-3 text-slate-600">{{ $r->category?->name }}</td>
            <td class="px-6 py-3 font-medium">Rp {{ number_format($r->total,0,',','.') }}</td>
            <td class="px-6 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium {{ $r->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($r->payment_status) }}
              </span>
            </td>
          </tr>
        @endforeach
        @if($recentRegistrations->isEmpty())
          <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada pendaftar</td></tr>
        @endif
      </tbody>
    </table>
  </div>
</div>
@endsection
