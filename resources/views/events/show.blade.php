@extends('layouts.app')
@section('title', $event->title)
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  {{-- Back --}}
  <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-primary-600 mb-6">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    Kembali ke Event
  </a>

  {{-- Hero Image --}}
  <div class="relative rounded-2xl overflow-hidden h-64 sm:h-80 lg:h-96 mb-8">
    <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
    <div class="absolute bottom-0 left-0 right-0 p-6">
      <div class="flex flex-wrap items-center gap-2 mb-3">
        <span class="px-3 py-1 bg-white/20 backdrop-blur-sm text-white text-sm font-medium rounded-full">{{ $event->location }}</span>
        @if($event->is_virtual) <span class="px-3 py-1 bg-blue-500 text-white text-sm font-medium rounded-full">Virtual Run</span> @endif
        @if($event->is_early_bird_active) <span class="px-3 py-1 bg-yellow-400 text-slate-900 text-sm font-semibold rounded-full">Early Bird Aktif</span> @endif
      </div>
      <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-white">{{ $event->title }}</h1>
    </div>
  </div>

  <div class="grid lg:grid-cols-3 gap-8">
    {{-- Left: Info --}}
    <div class="lg:col-span-2 space-y-6">
      {{-- Quick Info --}}
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-gray-200">
          <p class="text-xs text-slate-500 mb-1">Tanggal</p>
          <p class="font-semibold text-slate-900 text-sm">{{ $event->date->translatedFormat('d M Y') }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
          <p class="text-xs text-slate-500 mb-1">Waktu</p>
          <p class="font-semibold text-slate-900 text-sm">{{ $event->time }}</p>
        </div>
        <div class="bg-white rounded-xl p-4 border border-gray-200">
          <p class="text-xs text-slate-500 mb-1">Peserta</p>
          <p class="font-semibold text-slate-900 text-sm">{{ number_format($event->registered) }}/{{ number_format($event->slots) }}</p>
        </div>
      </div>

      {{-- Description --}}
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="font-display font-bold text-lg text-slate-900 mb-3">Tentang Event</h2>
        <p class="text-slate-600 leading-relaxed">{{ $event->description }}</p>
      </div>

      {{-- Highlights --}}
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="font-display font-bold text-lg text-slate-900 mb-4">Yang Kamu Dapatkan</h2>
        <div class="grid grid-cols-2 gap-3">
          @foreach($event->highlights as $h)
            <div class="flex items-center gap-2 text-slate-600">
              <svg class="w-5 h-5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span class="text-sm">{{ $h->highlight }}</span>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Venue --}}
      @if(!$event->is_virtual)
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
          <h2 class="font-display font-bold text-lg text-slate-900 mb-3">Lokasi</h2>
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <div>
              <p class="font-medium text-slate-900">{{ $event->venue }}</p>
              <p class="text-sm text-slate-500">{{ $event->location }}</p>
            </div>
          </div>
        </div>
      @endif

      {{-- Shirt Size Chart --}}
      <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h2 class="font-display font-bold text-lg text-slate-900 mb-4">Panduan Ukuran Kaos</h2>
        <p class="text-sm text-slate-500 mb-4">Semua ukuran dalam sentimeter (cm). <span class="text-blue-600 font-medium">Sport</span> lebih kecil selisih 1 ukuran.</p>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead>
              <tr class="border-b border-gray-200">
                <th class="pb-3 font-semibold text-slate-700">Ukuran</th>
                <th class="pb-3 font-semibold text-slate-700">Varian</th>
                <th class="pb-3 font-semibold text-slate-700">Lebar (cm)</th>
                <th class="pb-3 font-semibold text-slate-700">Panjang (cm)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @php
                $shirtSizes = [
                  'XXS' => ['default' => ['width' => '46', 'length' => '60']],
                  'XS' => ['sport' => ['width' => '46', 'length' => '60']],
                  'S' => ['sport' => ['width' => '48', 'length' => '62']],
                  'M' => ['sport' => ['width' => '50', 'length' => '65']],
                  'L' => ['sport' => ['width' => '52', 'length' => '68']],
                  'XL' => ['sport' => ['width' => '54', 'length' => '70']],
                  '2XL' => ['sport' => ['width' => '56', 'length' => '72']],
                  '3XL' => ['sport' => ['width' => '58', 'length' => '74']],
                  '4XL' => ['sport' => ['width' => '60', 'length' => '78']],
                ];
              @endphp
              @foreach($shirtSizes as $size => $variants)
                @foreach($variants as $variant => $dims)
                  <tr class="hover:bg-gray-50">
                    <td class="py-3 font-bold text-primary-600">{{ $size }}</td>
                    <td class="py-3 text-slate-600">{{ $variant === 'default' ? '-' : ucfirst($variant) }}</td>
                    <td class="py-3 text-slate-600">{{ $dims['width'] }}</td>
                    <td class="py-3 text-slate-600">{{ $dims['length'] }}</td>
                  </tr>
                @endforeach
              @endforeach
            </tbody>
          </table>
        </div>
        <p class="text-xs text-slate-400 mt-3">* Ukuran bisa berbeda ±2 cm. Disarankan memilih 1 ukuran lebih besar.</p>
      </div>
    </div>

    {{-- Right: Booking Card --}}
    <div class="lg:col-span-1">
      <div class="bg-white rounded-2xl border border-gray-200 p-6 sticky top-24">
        <h3 class="font-display font-bold text-lg text-slate-900 mb-2">Pilih Kategori</h3>
        @if($event->is_early_bird_active)
          <div class="flex items-center gap-2 mb-4 px-3 py-2 bg-yellow-50 rounded-lg border border-yellow-200">
            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-xs font-medium text-yellow-700">Early Bird hemat 10%! Berakhir {{ $event->early_bird_until->format('d M Y') }}</span>
          </div>
        @endif

        <div class="space-y-3 mb-6">
          @foreach($event->categories as $cat)
            <a href="{{ route('checkout.show', [$event->slug, $cat->id]) }}"
               class="flex items-center justify-between p-3 rounded-xl border-2 border-gray-200 hover:border-primary-500 hover:bg-primary-50 transition-colors group {{ !$cat->hasAvailableSlots() ? 'opacity-50 cursor-not-allowed hover:border-gray-200 hover:bg-white' : '' }}">
              <div>
                <span class="font-medium text-slate-900 group-hover:text-primary-700">{{ $cat->name }}</span>
                <p class="text-xs text-slate-500 mt-1">Kuota: {{ $cat->getRegistrationCount() }}/{{ $cat->limit }}</p>
              </div>
              <div class="text-right">
                @if($event->is_early_bird_active && $cat->early_bird_price)
                  <p class="text-xs line-through text-slate-400">Rp {{ number_format($cat->normal_price,0,',','.') }}</p>
                  <p class="font-bold text-yellow-600">Rp {{ number_format($cat->early_bird_price,0,',','.') }}</p>
                @else
                  <p class="font-bold text-primary-600">Rp {{ number_format($cat->normal_price,0,',','.') }}</p>
                @endif
              </div>
            </a>
          @endforeach
        </div>

        {{-- Slot progress --}}
        <div class="mb-4">
          <div class="flex justify-between text-xs text-slate-400 mb-1">
            <span>{{ number_format($event->registered) }} / {{ number_format($event->slots) }} slot terisi</span>
            <span>{{ $event->slot_percent }}%</span>
          </div>
          <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="bg-primary-500 h-2 rounded-full transition-all" style="width:{{ min($event->slot_percent,100) }}%"></div>
          </div>
        </div>
        <p class="text-xs text-slate-500 text-center">Dengan melanjutkan, kamu menyetujui Syarat & Ketentuan</p>
      </div>
    </div>
  </div>
</div>
@endsection
