@extends('layouts.admin')
@section('title', $event ? 'Edit Event' : 'Tambah Event')
@section('admin-content')
<div class="max-w-4xl">
  <form action="{{ $event ? route('admin.events.update',$event) : route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($event) @method('PUT') @endif

    @if($errors->any())
      <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    {{-- Basic Info --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
      <h2 class="font-display font-bold text-lg text-slate-800 mb-4">Informasi Dasar</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Judul Event *</label>
          <input type="text" name="title" value="{{ old('title', $event?->title) }}" required
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Kota/Lokasi *</label>
          <input type="text" name="location" value="{{ old('location', $event?->location) }}" placeholder="Jakarta" required
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Venue *</label>
          <input type="text" name="venue" value="{{ old('venue', $event?->venue) }}" placeholder="Monas, Jakarta Pusat" required
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal *</label>
          <input type="date" name="date" value="{{ old('date', $event?->date?->format('Y-m-d')) }}" required
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Waktu *</label>
          <input type="text" name="time" value="{{ old('time', $event?->time) }}" placeholder="05:00 WIB" required
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Slot Peserta *</label>
          <input type="number" name="slots" value="{{ old('slots', $event?->slots ?? 1000) }}" min="1" required
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Early Bird Berakhir</label>
          <input type="datetime-local" name="early_bird_until" value="{{ old('early_bird_until', $event?->early_bird_until?->format('Y-m-d\TH:i')) }}"
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
          <p class="text-xs text-slate-400 mt-1">Kosongkan jika tidak ada early bird. Harga early bird = harga normal – 10%</p>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi *</label>
          <textarea name="description" rows="4" required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none">{{ old('description', $event?->description) }}</textarea>
        </div>
      </div>
    </div>

    {{-- Foto --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
      <h2 class="font-display font-bold text-lg text-slate-800 mb-4">Foto Event</h2>
      @if($event?->image)
        <div class="mb-4">
          <img src="{{ $event->image_url }}" class="h-40 rounded-xl object-cover" alt="Current image">
          <p class="text-xs text-slate-400 mt-2">Foto saat ini. Upload baru untuk mengganti.</p>
        </div>
      @endif
      <input type="file" name="image" accept="image/*"
             class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
      <p class="text-xs text-slate-400 mt-2">Format: JPG, PNG, WebP. Rasio 3:2 direkomendasikan.</p>
    </div>

    {{-- Categories --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-display font-bold text-lg text-slate-800">Kategori & Harga</h2>
        <button type="button" onclick="addCategory()" class="px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 text-sm font-medium rounded-lg transition-colors">+ Tambah</button>
      </div>
      <p class="text-xs text-slate-500 mb-4">Harga early bird dihitung otomatis (harga normal – 10%) saat event memiliki tanggal early bird.</p>
      <div id="categoriesContainer" class="space-y-3">
        @if($event)
          @foreach($event->categories as $i => $cat)
            <div class="category-row flex items-center gap-3">
              <input type="text" name="categories[{{ $i }}][name]" value="{{ $cat->name }}" placeholder="Nama kategori (cth: 5K)" required
                     class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
              <div class="flex items-center gap-1.5">
                <span class="text-sm text-slate-500">Rp</span>
                <input type="number" name="categories[{{ $i }}][normal_price]" value="{{ $cat->normal_price }}" placeholder="Harga normal" required min="0"
                       class="w-36 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
              </div>
              <button type="button" onclick="this.closest('.category-row').remove()" class="text-red-400 hover:text-red-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          @endforeach
        @else
          <div class="category-row flex items-center gap-3">
            <input type="text" name="categories[0][name]" placeholder="Nama kategori (cth: 5K)" required class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <div class="flex items-center gap-1.5"><span class="text-sm text-slate-500">Rp</span>
              <input type="number" name="categories[0][normal_price]" placeholder="Harga normal" required min="0" class="w-36 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            <button type="button" onclick="this.closest('.category-row').remove()" class="text-red-400 hover:text-red-600 p-1">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        @endif
      </div>
    </div>

    {{-- Highlights --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-display font-bold text-lg text-slate-800">Keuntungan Peserta</h2>
        <button type="button" onclick="addHighlight()" class="px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 text-sm font-medium rounded-lg transition-colors">+ Tambah</button>
      </div>
      <div id="highlightsContainer" class="space-y-2">
        @php $highlights = $event ? $event->highlights->pluck('highlight')->toArray() : ['']; @endphp
        @foreach($highlights as $i => $h)
          <div class="highlight-row flex items-center gap-3">
            <input type="text" name="highlights[{{ $i }}]" value="{{ $h }}" placeholder="cth: Medali finisher eksklusif"
                   class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <button type="button" onclick="this.closest('.highlight-row').remove()" class="text-red-400 hover:text-red-600 p-1">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Toggles --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
      <h2 class="font-display font-bold text-lg text-slate-800 mb-4">Pengaturan Tambahan</h2>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
          ['name'=>'is_active', 'label'=>'Event Aktif', 'default'=>true],
          ['name'=>'is_virtual', 'label'=>'Virtual Run', 'default'=>false],
          ['name'=>'is_beginner', 'label'=>'Ramah Pemula', 'default'=>false],
          ['name'=>'has_medal', 'label'=>'Ada Medali', 'default'=>true],
          ['name'=>'is_weekend', 'label'=>'Akhir Pekan', 'default'=>false],
        ] as $toggle)
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="hidden" name="{{ $toggle['name'] }}" value="0">
            <input type="checkbox" name="{{ $toggle['name'] }}" value="1"
                   {{ old($toggle['name'], $event ? ($event->{$toggle['name']} ? '1' : '0') : ($toggle['default'] ? '1' : '0')) == '1' ? 'checked' : '' }}
                   class="w-4 h-4 rounded text-green-600 focus:ring-green-500">
            <span class="text-sm text-slate-700">{{ $toggle['label'] }}</span>
          </label>
        @endforeach
      </div>
    </div>

    <div class="flex items-center gap-4">
      <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors">
        {{ $event ? 'Simpan Perubahan' : 'Buat Event' }}
      </button>
      <a href="{{ route('admin.events.index') }}" class="px-6 py-3 bg-white border border-gray-200 hover:bg-gray-50 text-slate-700 font-medium rounded-xl transition-colors">Batal</a>
    </div>
  </form>
</div>
@endsection
@push('scripts')
<script>
  let catIdx = {{ $event ? $event->categories->count() : 1 }};
  let hlIdx  = {{ $event ? $event->highlights->count() : 1 }};

  function addCategory() {
    const container = document.getElementById('categoriesContainer');
    const div = document.createElement('div');
    div.className = 'category-row flex items-center gap-3';
    div.innerHTML = `
      <input type="text" name="categories[${catIdx}][name]" placeholder="Nama kategori (cth: 10K)" required
             class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      <div class="flex items-center gap-1.5"><span class="text-sm text-slate-500">Rp</span>
        <input type="number" name="categories[${catIdx}][normal_price]" placeholder="Harga" required min="0"
               class="w-36 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      </div>
      <button type="button" onclick="this.closest('.category-row').remove()" class="text-red-400 hover:text-red-600 p-1">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>`;
    container.appendChild(div);
    catIdx++;
  }

  function addHighlight() {
    const container = document.getElementById('highlightsContainer');
    const div = document.createElement('div');
    div.className = 'highlight-row flex items-center gap-3';
    div.innerHTML = `
      <input type="text" name="highlights[${hlIdx}]" placeholder="cth: E-certificate"
             class="flex-1 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
      <button type="button" onclick="this.closest('.highlight-row').remove()" class="text-red-400 hover:text-red-600 p-1">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>`;
    container.appendChild(div);
    hlIdx++;
  }
</script>
@endpush
