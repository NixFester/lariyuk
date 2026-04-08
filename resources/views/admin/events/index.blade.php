@extends('layouts.admin')
@section('title','Kelola Event')
@section('admin-content')
<div class="flex items-center justify-between mb-6">
  <div></div>
  <a href="{{ route('admin.events.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors">+ Tambah Event</a>
</div>
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="bg-gray-50 text-left">
        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Event</th>
        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Lokasi</th>
        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Peserta</th>
        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Kategori</th>
        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Early Bird</th>
        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
        <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Aksi</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100">
        @foreach($events as $event)
        <tr class="hover:bg-gray-50">
          <td class="px-5 py-4"><div class="flex items-center gap-3">
            <img src="{{ $event->image_url }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
            <div><p class="font-semibold text-slate-900">{{ $event->title }}</p><p class="text-xs text-slate-400">{{ $event->categories->count() }} kategori &bull; {{ $event->registrations->count() }} pendaftar</p></div>
          </div></td>
          <td class="px-5 py-4 text-slate-600">{{ $event->date->format('d M Y') }}</td>
          <td class="px-5 py-4 text-slate-600">{{ $event->location }}</td>
          <td class="px-5 py-4"><p class="font-medium">{{ number_format($event->registered) }}/{{ number_format($event->slots) }}</p>
            <div class="w-20 bg-gray-100 rounded-full h-1 mt-1"><div class="bg-green-500 h-1 rounded-full" style="width:{{ min($event->slot_percent,100) }}%"></div></div></td>
          <td class="px-5 py-4"><div class="space-y-1">
            @foreach($event->categories as $cat)
              <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-medium text-slate-600">{{ $cat->name }}</span>
                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-medium rounded">{{ $cat->getRegistrationCount() }}/{{ $cat->limit }}</span>
              </div>
            @endforeach
          </div></td>
          <td class="px-5 py-4">@if($event->early_bird_until)
            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $event->is_early_bird_active ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' }}">{{ $event->is_early_bird_active ? 'Aktif' : 'Berakhir' }}</span>
            <p class="text-xs text-slate-400 mt-0.5">{{ $event->early_bird_until->format('d M Y') }}</p>
          @else<span class="text-slate-400 text-xs">–</span>@endif</td>
          <td class="px-5 py-4"><span class="px-2 py-1 rounded-full text-xs font-medium {{ $event->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">{{ $event->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td class="px-5 py-4"><div class="flex items-center gap-2">
            <a href="{{ route('admin.events.edit', $event) }}" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium rounded-lg transition-colors">Edit</a>
            <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Hapus event ini?')">
              @csrf @method('DELETE')
              <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-medium rounded-lg transition-colors">Hapus</button>
            </form>
          </div></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($events->hasPages())<div class="px-5 py-4 border-t border-gray-100">{{ $events->links() }}</div>@endif
</div>
@endsection
