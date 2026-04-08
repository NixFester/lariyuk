@extends('layouts.app')
@section('title','Semua Event')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="font-display text-3xl font-bold text-slate-900 mb-2">Event Lari Mendatang</h1>
  <p class="text-slate-500 mb-8">Temukan dan daftarkan dirimu di event lari terbaik</p>

  {{-- Filters --}}
  <form method="GET" class="bg-white rounded-2xl border border-gray-200 p-4 mb-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="Cari event..."
               class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>
      <select name="location" class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Semua Lokasi</option>
        @foreach($locations as $loc)
          <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
        @endforeach
      </select>
      <select name="category" class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        <option value="">Semua Kategori</option>
        @foreach(['5K','10K','Half Marathon','Full Marathon'] as $cat)
          <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
      </select>
      <div class="flex gap-2">
        <select name="sort" class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
          <option value="date_asc" {{ $sort === 'date_asc' ? 'selected' : '' }}>Tanggal Terdekat</option>
          <option value="date_desc" {{ $sort === 'date_desc' ? 'selected' : '' }}>Tanggal Terjauh</option>
        </select>
        <button type="submit" class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors">Cari</button>
      </div>
    </div>
  </form>

  {{-- Grid --}}
  @if($events->isEmpty())
    <div class="text-center py-16">
      <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <h3 class="font-semibold text-slate-600 mb-2">Tidak ada event ditemukan</h3>
      <a href="{{ route('events.index') }}" class="text-sm text-green-600 hover:underline">Reset filter</a>
    </div>
  @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
      @foreach($events as $event)
        <div class="bg-white rounded-2xl overflow-hidden border border-gray-200 card-hover">
          <div class="relative">
            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-48 object-cover">
            <div class="absolute top-3 left-3">
              <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-xs font-medium text-slate-700 rounded-full">{{ $event->location }}</span>
            </div>
            <div class="absolute top-3 right-3 flex gap-1">
              @if($event->is_virtual)<span class="px-2.5 py-1 bg-blue-500 text-white text-xs font-medium rounded-full">Virtual</span>@endif
              @if($event->is_early_bird_active)<span class="px-2.5 py-1 bg-yellow-400 text-slate-900 text-xs font-semibold rounded-full">Early Bird</span>@endif
              @if($event->is_almost_full && !$event->is_early_bird_active)<span class="px-2.5 py-1 bg-orange-500 text-white text-xs font-medium rounded-full">Hampir Penuh</span>@endif
            </div>
          </div>
          <div class="p-5">
            <h3 class="font-display font-bold text-slate-900 mb-1">{{ $event->title }}</h3>
            <p class="text-sm text-slate-500 mb-1">{{ $event->date->translatedFormat('d M Y') }} &bull; {{ $event->time }}</p>
            <p class="text-xs text-slate-400 mb-3 flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              {{ $event->venue }}
            </p>
            <div class="flex flex-wrap gap-1.5 mb-4">
              @foreach($event->categories->take(3) as $cat)
                <span class="px-2 py-1 bg-gray-100 text-xs font-medium text-slate-600 rounded-md">{{ $cat->name }} ({{ $cat->getRegistrationCount() }}/{{ $cat->limit }})</span>
              @endforeach
              @if($event->categories->count() > 3)
                <span class="px-2 py-1 bg-gray-100 text-xs font-medium text-slate-500 rounded-md">+{{ $event->categories->count()-3 }}</span>
              @endif
            </div>
            {{-- Progress --}}
            <div class="mb-4">
              <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span>{{ number_format($event->registered) }} / {{ number_format($event->slots) }}</span>
                <span>{{ $event->slot_percent }}% terisi</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $event->slot_percent >= 90 ? 'bg-orange-500' : 'bg-green-500' }}" style="width:{{ min($event->slot_percent,100) }}%"></div>
              </div>
            </div>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
              <div>
                @if($event->is_early_bird_active)
                  <p class="text-xs text-yellow-600 font-medium">Early Bird</p>
                  <p class="font-bold text-green-600">Rp {{ number_format($event->categories->min('early_bird_price'),0,',','.') }}</p>
                @else
                  <p class="text-xs text-slate-400">Mulai dari</p>
                  <p class="font-bold text-green-600">Rp {{ number_format($event->categories->min('normal_price'),0,',','.') }}</p>
                @endif
              </div>
              <a href="{{ route('events.show', $event->slug) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">Daftar</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <div class="flex justify-center">{{ $events->withQueryString()->links() }}</div>
  @endif
</div>
@endsection
