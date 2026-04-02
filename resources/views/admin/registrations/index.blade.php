@extends('layouts.admin')
@section('title','Data Pendaftar')
@section('admin-content')

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-40">
      <label class="block text-xs font-medium text-slate-500 mb-1">Cari</label>
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama, BIB, email, KTP..."
             class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
    </div>
    <div class="min-w-40">
      <label class="block text-xs font-medium text-slate-500 mb-1">Event</label>
      <select name="event_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Semua Event</option>
        @foreach($events as $e)
          <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>{{ $e->title }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
      <select name="status" class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Semua Status</option>
        @foreach(['paid','pending','failed','expired'] as $s)
          <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">Cari</button>
    <a href="{{ route('admin.registrations.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-slate-600 text-sm font-medium rounded-lg transition-colors">Reset</a>

    {{-- Export --}}
    <a href="{{ route('admin.registrations.export', request()->only('event_id','status')) }}"
       class="ml-auto px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Export ke Spreadsheet
    </a>
  </form>
</div>

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
  <div class="px-5 py-3 border-b border-gray-100 text-sm text-slate-500">
    Menampilkan {{ $registrations->firstItem() }}–{{ $registrations->lastItem() }} dari {{ $registrations->total() }} pendaftar
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-gray-50 text-left">
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nickname (BIB)</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nama &amp; KTP</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Kontak</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Gol. Darah</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Kaos</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Event / Kategori</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Total</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Aksi</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($registrations as $r)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono font-bold text-green-600 text-sm">{{ $r->nickname }}</td>
            <td class="px-4 py-3">
              <p class="font-semibold text-slate-900">{{ $r->nama_peserta }}</p>
              <p class="text-xs text-slate-400 font-mono">KTP: {{ $r->no_ktp }}</p>
              <p class="text-xs text-slate-400">{{ $r->tanggal_lahir?->format('d/m/Y') }} &bull; {{ $r->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            </td>
            <td class="px-4 py-3">
              <p class="text-slate-700">{{ $r->email }}</p>
              <p class="text-xs text-slate-400">{{ $r->phone }}</p>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 bg-red-50 text-red-700 rounded font-bold text-xs">{{ $r->golongan_darah }}</span>
            </td>
            <td class="px-4 py-3 font-bold text-slate-700">{{ $r->ukuran_kaos }}</td>
            <td class="px-4 py-3">
              <p class="text-slate-700 text-xs leading-tight max-w-xs">{{ $r->event?->title }}</p>
              <span class="inline-block mt-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-medium">{{ $r->category?->name }}</span>
              @if($r->is_early_bird)
                <span class="inline-block ml-1 px-2 py-0.5 bg-yellow-50 text-yellow-700 rounded text-xs">EB</span>
              @endif
            </td>
            <td class="px-4 py-3 font-medium text-slate-900">Rp {{ number_format($r->total,0,',','.') }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 rounded-full text-xs font-medium
                {{ $r->payment_status === 'paid' ? 'bg-green-100 text-green-700' :
                   ($r->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-600') }}">
                {{ ucfirst($r->payment_status) }}
              </span>
            </td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <a href="{{ route('admin.registrations.show', $r->id) }}" class="px-2 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-lg transition-colors">Detail</a>
                <form action="{{ route('admin.registrations.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus data pendaftar ini?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="px-2 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium rounded-lg transition-colors">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @endforeach
        @if($registrations->isEmpty())
          <tr><td colspan="9" class="px-4 py-12 text-center text-slate-400">Tidak ada data pendaftar</td></tr>
        @endif
      </tbody>
    </table>
  </div>
  @if($registrations->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $registrations->links() }}</div>
  @endif
</div>
@endsection
