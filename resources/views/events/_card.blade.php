{{-- resources/views/events/_card.blade.php --}}
<div class="bg-white rounded-2xl overflow-hidden border border-surface-200 card-hover">
  <div class="relative">
    <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-48 object-cover">
    <div class="absolute top-3 left-3">
      <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm text-xs font-medium text-slate-700 rounded-full">
        {{ $event->location }}
      </span>
    </div>
    <div class="absolute top-3 right-3 flex gap-1.5">
      @if($event->is_virtual)
        <span class="px-2.5 py-1 bg-blue-500 text-white text-xs font-medium rounded-full">Virtual</span>
      @elseif($event->is_almost_full)
        <span class="px-2.5 py-1 bg-red-500 text-white text-xs font-medium rounded-full">Hampir Penuh</span>
      @endif
      @if($event->is_early_bird_active)
        <span class="px-2.5 py-1 bg-accent-500 text-white text-xs font-medium rounded-full">Early Bird</span>
      @endif
    </div>
  </div>

  <div class="p-5">
    <h3 class="font-display font-bold text-slate-900 mb-2 line-clamp-1">{{ $event->title }}</h3>
    <div class="flex items-center gap-1.5 text-sm text-slate-500 mb-1">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      {{ \Carbon\Carbon::parse($event->date)->translatedFormat('d F Y') }}
    </div>
    <div class="flex items-center gap-1.5 text-sm text-slate-500 mb-4 truncate">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
      </svg>
      <span class="truncate">{{ $event->venue }}</span>
    </div>

    {{-- Category tags --}}
    <div class="flex flex-wrap gap-1.5 mb-4">
      @foreach($event->categories->take(3) as $cat)
        <span class="px-2 py-1 bg-surface-100 text-xs font-medium text-slate-600 rounded-md">{{ $cat->name }}</span>
      @endforeach
    </div>

    <div class="flex items-center justify-between pt-4 border-t border-surface-200">
      <div>
        <p class="text-xs text-slate-400">Mulai dari</p>
        @php
          $lowestPrice = $event->categories->min('active_price');
          $wasPrice    = $event->is_early_bird_active ? $event->categories->min('normal_price') : null;
        @endphp
        @if($wasPrice && $wasPrice > $lowestPrice)
          <p class="text-xs text-slate-400 line-through">Rp {{ number_format($wasPrice,0,',','.') }}</p>
        @endif
        <p class="font-bold text-primary-600">Rp {{ number_format($lowestPrice,0,',','.') }}</p>
      </div>
      <a href="{{ route('events.show', $event->slug) }}"
         class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors btn-press">
        Daftar
      </a>
    </div>
  </div>
</div>
