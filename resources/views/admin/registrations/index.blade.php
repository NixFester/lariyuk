@extends('layouts.admin')
@section('title','Data Pendaftar')
@section('admin-content')

<div class="space-y-4">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Daftar Pendaftar</h1>
  </div>

  @if($events->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
      <p class="text-slate-400">Belum ada event</p>
    </div>
  @else
    <div class="grid gap-4">
      @foreach($events as $event)
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
          <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-slate-100">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">{{ $event->title }}</h2>
                <p class="text-sm text-slate-500 mt-1">
                  {{ $event->date?->format('d M Y') }} • {{ $event->location }}
                </p>
              </div>
              <div class="text-right">
                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">
                  {{ $event->registrations()->count() }} Pendaftar
                </span>
              </div>
            </div>
          </div>

          @php
            $categories = $event->categories()->withCount('registrations')->get();
          @endphp

          @if($categories->isEmpty())
            <div class="px-6 py-8 text-center text-slate-400">
              Belum ada kategori untuk event ini
            </div>
          @else
            <div class="divide-y divide-gray-100">
              @foreach($categories as $category)
                <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <h3 class="font-semibold text-slate-900">{{ $category->name }}</h3>
                      <p class="text-sm text-slate-500 mt-1">
                        {{ $category->registrations_count }} Pendaftar
                        @if($category->registrations_count > 0)
                          <span class="mx-2">•</span>
                          {{ $category->normal_price > 0 ? 'Rp ' . number_format($category->normal_price, 0, ',', '.') : 'Gratis' }}
                        @endif
                      </p>
                    </div>
                    <div class="flex items-center gap-2">
                      @if($category->registrations_count > 0)
                        <a href="{{ route('admin.registrations.by-category', [$event->id, $category->id]) }}"
                           class="px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition-colors">
                          Lihat Semua
                        </a>
                        <a href="{{ route('admin.registrations.export-by-category', [$event->id, $category->id]) }}"
                           class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                          </svg>
                          Export
                        </a>
                      @else
                        <span class="text-sm text-slate-400 italic">Tidak ada pendaftar</span>
                      @endif
                    </div>
                  </div>

                  @php
                    $latestRegistrations = $category->registrations()
                      ->with(['event', 'category'])
                      ->latest()
                      ->take(3)
                      ->get();
                  @endphp

                  @if($latestRegistrations->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100 bg-gray-50 rounded-lg p-3 space-y-2">
                      <p class="text-xs font-medium text-slate-500">Pendaftar Terbaru:</p>
                      @foreach($latestRegistrations as $registration)
                        <div class="flex items-center justify-between text-sm">
                          <div>
                            <p class="font-mono font-bold text-green-600">{{ $registration->nickname }}</p>
                            <p class="text-xs text-slate-500">{{ $registration->nama_peserta }}</p>
                          </div>
                          <div class="text-right">
                            <span class="inline-block px-2 py-1 rounded text-xs font-medium
                              {{ $registration->payment_status === 'paid' ? 'bg-green-100 text-green-700' :
                                 ($registration->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-600') }}">
                              {{ ucfirst($registration->payment_status) }}
                            </span>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @endif
</div>

@endsection
