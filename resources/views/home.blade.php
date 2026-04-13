@extends('layouts.app')
@section('title','Beranda')
@section('content')
{{-- Hero --}}
<section class="py-16 lg:py-24 px-4" style="background:radial-gradient(circle at 20% 50%,rgba(0,163,95,.08) 0%,transparent 50%),radial-gradient(circle at 80% 20%,rgba(255,107,53,.06) 0%,transparent 40%)">
  <div class="max-w-7xl mx-auto">
    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-medium mb-6">
      <span class="w-2 h-2 bg-green-500 rounded-full pulse-dot"></span>
      Event Terbaru Tersedia
    </div>
    <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight mb-6">
      Temukan Event Lari <span class="text-primary-600">Favoritmu</span>
    </h1>
    <p class="text-lg text-slate-600 mb-8 max-w-lg">Platform terpercaya untuk pendaftaran event lari di Indonesia. Bergabunglah dengan ribuan pelari dan raih prestasi terbaikmu.</p>
    <div class="flex gap-3">
      <a href="{{ route('events.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-accent-500 hover:bg-accent-600 text-white font-semibold rounded-xl transition-colors btn-press">
        Jelajahi Event
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </a>
    </div>
  </div>
</section>

{{-- Stats --}}
<section class="bg-white border-y border-gray-200">
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
      <div><p class="text-3xl font-bold text-primary-600">150+</p><p class="text-sm text-slate-500">Event Terselenggara</p></div>
      <div><p class="text-3xl font-bold text-primary-600">50K+</p><p class="text-sm text-slate-500">Pelari Terdaftar</p></div>
      <div><p class="text-3xl font-bold text-primary-600">34</p><p class="text-sm text-slate-500">Provinsi</p></div>
      <div><p class="text-3xl font-bold text-primary-600">4.9</p><p class="text-sm text-slate-500">Rating Pengguna</p></div>
    </div>
  </div>
</section>

{{-- Events --}}
<section class="py-12 lg:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between mb-8">
      <div>
        <h2 class="font-display text-2xl sm:text-3xl font-bold text-slate-900">Event Lari Mendatang</h2>
        <p class="text-slate-500 mt-1">Temukan dan daftarkan dirimu di event lari terbaik</p>
      </div>
      <a href="{{ route('events.index') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700">Lihat Semua &rarr;</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($events as $event)
        <div class="relative bg-white rounded-2xl overflow-hidden border border-gray-200 card-hover {{ $event->is_active ? '' : 'bg-gray-100 border-gray-300 opacity-80' }}">
          <div class="relative">
            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-48 object-cover">
            <div class="absolute top-3 left-3">
              <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-xs font-medium text-slate-700 rounded-full">{{ $event->location }}</span>
            </div>
            <div class="absolute top-3 right-3">
              @if($event->is_virtual)
                <span class="px-2.5 py-1 bg-blue-500 text-white text-xs font-medium rounded-full">Virtual</span>
              @elseif($event->is_almost_full)
                <span class="px-2.5 py-1 bg-orange-500 text-white text-xs font-medium rounded-full">Hampir Penuh</span>
              @endif
              @if($event->is_early_bird_active)
                <span class="px-2.5 py-1 bg-yellow-400 text-slate-900 text-xs font-medium rounded-full ml-1">Early Bird</span>
              @endif
            </div>
            @unless($event->is_active)
              <div class="absolute inset-0 bg-slate-900/60 flex items-center justify-center text-center px-4">
                <span class="rounded-full bg-white/10 px-4 py-2 text-sm font-semibold uppercase tracking-[0.12em] text-white">Pendaftaran Ditutup Sementara</span>
              </div>
            @endunless
          </div>
          <div class="p-5">
            <h3 class="font-display font-bold text-slate-900 mb-1 truncate">{{ $event->title }}</h3>
            <p class="text-sm text-slate-500 mb-3">{{ $event->date->translatedFormat('d M Y') }} &bull; {{ $event->time }}</p>
            <div class="flex flex-wrap gap-1.5 mb-4">
              @foreach($event->categories->take(3) as $cat)
                <span class="px-2 py-1 bg-gray-100 text-xs font-medium text-slate-600 rounded-md">{{ $cat->name }}</span>
              @endforeach
            </div>
            {{-- Slot progress bar --}}
            <div class="mb-4">
              <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span>{{ number_format($event->registered) }} / {{ number_format($event->slots) }} peserta</span>
                <span>{{ $event->slot_percent }}%</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="bg-primary-500 h-1.5 rounded-full" style="width:{{ min($event->slot_percent,100) }}%"></div>
              </div>
            </div>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
              <div>
                @if($event->is_early_bird_active)
                  <p class="text-xs text-yellow-600 font-medium">Early Bird</p>
                  <p class="font-bold text-primary-600">Rp {{ number_format($event->categories->min('early_bird_price'),0,',','.') }}</p>
                @else
                  <p class="text-xs text-slate-400">Mulai dari</p>
                  <p class="font-bold text-primary-600">Rp {{ number_format($event->categories->min('normal_price'),0,',','.') }}</p>
                @endif
              </div>
              <a href="{{ route('events.show', $event->slug) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors btn-press">Daftar</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    @if($events->hasPages())
      <div class="flex justify-center mt-10">{{ $events->links() }}</div>
    @endif
  </div>
</section>
@endsection
