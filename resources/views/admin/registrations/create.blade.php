@extends('layouts.admin')
@section('title','Tambah Pendaftar')
@section('admin-content')

<div class="space-y-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-900">Tambah Pendaftar</h1>
      <p class="text-sm text-slate-500">Input data peserta langsung ke database tanpa mengirim email.</p>
    </div>
    <a href="{{ route('admin.registrations.index') }}" class="text-sm font-medium text-slate-700 hover:text-green-600">Kembali ke daftar pendaftar</a>
  </div>

  @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
      {{ session('error') }}
    </div>
  @endif

  <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm">
    <form action="{{ route('admin.registrations.store') }}" method="POST" class="space-y-6">
      @csrf

      <div class="grid gap-6 lg:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Event</label>
          <select id="event-select" name="event_id" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">Pilih event</option>
            @foreach($events as $event)
              <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>{{ $event->title }}</option>
            @endforeach
          </select>
          @error('event_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Kategori</label>
          <select id="category-select" name="event_category_id" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">Pilih kategori</option>
          </select>
          @error('event_category_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">No. KTP</label>
          <input type="text" name="no_ktp" value="{{ old('no_ktp') }}" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100" />
          @error('no_ktp') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Nama Peserta</label>
          <input type="text" name="nama_peserta" value="{{ old('nama_peserta') }}" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100" />
          @error('nama_peserta') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Nickname (BIB)</label>
          <input type="text" name="nickname" value="{{ old('nickname') }}" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100" />
          @error('nickname') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100" />
          @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">No. HP</label>
          <input type="text" name="phone" value="{{ old('phone') }}" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100" />
          @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100" />
          @error('tanggal_lahir') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">Pilih jenis kelamin</option>
            <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
            <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
          </select>
          @error('jenis_kelamin') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Ukuran Kaos</label>
          <select name="ukuran_kaos" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">Pilih ukuran kaos</option>
            @foreach(['XXS','XS-sport','S-sport','M-sport','L-sport','XL-sport','2XL-sport','3XL-sport','4XL-sport'] as $size)
              <option value="{{ $size }}" {{ old('ukuran_kaos') === $size ? 'selected' : '' }}>{{ $size }}</option>
            @endforeach
          </select>
          @error('ukuran_kaos') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Golongan Darah</label>
          <select name="golongan_darah" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100">
            <option value="">Pilih golongan darah</option>
            @foreach(['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $blood)
              <option value="{{ $blood }}" {{ old('golongan_darah') === $blood ? 'selected' : '' }}>{{ $blood }}</option>
            @endforeach
          </select>
          @error('golongan_darah') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-slate-700">Kontak Darurat (Nama)</label>
          <input type="text" name="kontak_darurat_nama" value="{{ old('kontak_darurat_nama') }}" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100" />
          @error('kontak_darurat_nama') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Kontak Darurat (No. HP)</label>
          <input type="text" name="kontak_darurat_hp" value="{{ old('kontak_darurat_hp') }}" class="mt-2 block w-full rounded-2xl border-gray-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-100" />
          @error('kontak_darurat_hp') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="text-sm text-slate-500">
        Data akan disimpan sebagai pendaftaran dengan status <strong>Lunas</strong>. Tidak ada email tiket yang dikirim.
      </div>

      <div class="flex items-center justify-between gap-4">
        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-slate-900/5 hover:bg-green-700 transition">
          Simpan Data
        </button>
        <a href="{{ route('admin.registrations.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">Batal</a>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
  const events = @json($eventsData);

  function setCategoriesForEvent(eventId) {
    const categorySelect = document.getElementById('category-select');
    categorySelect.innerHTML = '<option value="">Pilih kategori</option>';
    const selectedEvent = events.find(e => e.id === Number(eventId));

    if (!selectedEvent) {
      return;
    }

    selectedEvent.categories.forEach(category => {
      const option = document.createElement('option');
      option.value = category.id;
      option.textContent = category.name;
      if (String(category.id) === String('{{ old('event_category_id') }}')) {
        option.selected = true;
      }
      categorySelect.appendChild(option);
    });
  }

  document.getElementById('event-select')?.addEventListener('change', function () {
    setCategoriesForEvent(this.value);
  });

  if ('{{ old('event_id') }}') {
    setCategoriesForEvent('{{ old('event_id') }}');
  }
</script>
@endpush

@endsection
