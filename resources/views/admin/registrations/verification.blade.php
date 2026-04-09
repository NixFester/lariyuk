@extends('layouts.admin')
@section('title','Verifikasi Pembayaran')
@section('admin-content')

{{-- Filters --}}
<div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
  <form method="GET" class="flex flex-wrap gap-3 items-end">
    <div class="min-w-40">
      <label class="block text-xs font-medium text-slate-500 mb-1">Event</label>
      <select name="event_id" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Semua Event</option>
        @foreach($events as $e)
          <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>{{ $e->title }}</option>
        @endforeach
      </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">Cari</button>
    <a href="{{ route('admin.registrations.verification') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-slate-600 text-sm font-medium rounded-lg transition-colors">Reset</a>
  </form>
</div>

{{-- Status Messages --}}
@if(session('success'))
  <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
    <p class="text-green-700 text-sm font-medium">✓ {{ session('success') }}</p>
  </div>
@endif

@if(session('warning'))
  <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
    <p class="text-yellow-700 text-sm font-medium">⚠ {{ session('warning') }}</p>
  </div>
@endif


<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
  <div class="px-5 py-3 border-b border-gray-100 text-sm text-slate-500">
    <strong>{{ $registrations->total() }}</strong> pendaftar menunggu verifikasi pembayaran (WA dikonfirmasi)
  </div>

  @if($registrations->count() > 0)
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left">
          <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nickname (BIB)</th>
          <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nama Peserta</th>
          <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Email</th>
          <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Event / Kategori</th>
          <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Total</th>
          <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Konfirmasi WA</th>
          <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($registrations as $r)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 font-mono font-bold text-green-600 text-sm">{{ $r->nickname }}</td>
              <td class="px-4 py-3">
                <p class="font-semibold text-slate-900">{{ $r->nama_peserta }}</p>
                <p class="text-xs text-slate-400 font-mono">{{ $r->no_ktp }}</p>
              </td>
              <td class="px-4 py-3 text-slate-700 text-xs">{{ $r->email }}</td>
              <td class="px-4 py-3">
                <p class="text-slate-700 text-xs leading-tight">{{ $r->event?->title }}</p>
                <span class="inline-block mt-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-medium">{{ $r->category?->name }}</span>
              </td>
              <td class="px-4 py-3 font-medium text-slate-900">Rp {{ number_format($r->total,0,',','.') }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                  {{ $r->whatsapp_confirmed_at?->translatedFormat('d/m H:i') ?? '-' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <form action="{{ route('admin.registrations.verify-payment', $r->id) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran {{ $r->invoice_number }}?')" class="inline">
                  @csrf
                  <button type="submit" class="px-3 py-1.5 bg-green-50 hover:bg-green-100 text-green-700 text-xs font-semibold rounded-lg transition-colors">
                    ✓ Verifikasi
                  </button>
                </form>
                <a href="{{ route('admin.registrations.show', $r->id) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-lg transition-colors inline-block ml-2">Detail</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    @if($registrations->lastPage() > 1)
      <div class="px-5 py-4 border-t border-gray-100">
        {{ $registrations->links() }}
      </div>
    @endif
  @else
    <div class="px-5 py-12 text-center">
      <p class="text-slate-500 text-sm">Tidak ada pendaftar yang menunggu verifikasi.</p>
    </div>
  @endif
</div>

{{-- Pending Registrations without WA Confirmation --}}
@if($pendingRegistrations->count() > 0)
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
  <div class="px-5 py-3 border-b border-gray-100 text-sm text-slate-500">
    <strong>{{ $pendingRegistrations->total() }}</strong> pendaftar menunggu konfirmasi WhatsApp
  </div>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-gray-50 text-left">
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nickname (BIB)</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Nama Peserta</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Email</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Event / Kategori</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Total</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Dibuat</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Aksi</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($pendingRegistrations as $r)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 font-mono font-bold text-orange-600 text-sm">{{ $r->nickname }}</td>
            <td class="px-4 py-3">
              <p class="font-semibold text-slate-900">{{ $r->nama_peserta }}</p>
              <p class="text-xs text-slate-400 font-mono">{{ $r->no_ktp }}</p>
            </td>
            <td class="px-4 py-3 text-slate-700 text-xs">{{ $r->email }}</td>
            <td class="px-4 py-3">
              <p class="text-slate-700 text-xs leading-tight">{{ $r->event?->title }}</p>
              <span class="inline-block mt-1 px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-medium">{{ $r->category?->name }}</span>
            </td>
            <td class="px-4 py-3 font-medium text-slate-900">Rp {{ number_format($r->total,0,',','.') }}</td>
            <td class="px-4 py-3 text-slate-500 text-xs">{{ $r->created_at?->translatedFormat('d/m H:i') }}</td>
            <td class="px-4 py-3">
              <form action="{{ route('admin.registrations.skip-payment', $r->id) }}" method="POST" onsubmit="return confirm('Lewati pembayaran untuk {{ $r->invoice_number }}?')" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-semibold rounded-lg transition-colors">
                  ✓ Verifikasi
                </button>
              </form>
              <a href="{{ route('admin.registrations.show', $r->id) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-lg transition-colors inline-block ml-2">Detail</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  @if($pendingRegistrations->lastPage() > 1)
    <div class="px-5 py-4 border-t border-gray-100">
      {{ $pendingRegistrations->links() }}
    </div>
  @endif
</div>
@endif


@endsection
