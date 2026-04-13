@extends('layouts.app')
@section('title', 'Registrasi Ulang – Sistem Error')
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex items-center gap-4 mb-8">
    <a href="{{ route('events.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
      <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
      <h1 class="font-display text-2xl font-bold text-slate-900">Registrasi Ulang</h1>
      <p class="text-sm text-slate-500">Karena Sistem Error – Silakan Lengkapi Data Ulang</p>
    </div>
  </div>

  {{-- APOLOGY NOTICE --}}
  <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-6 mb-8">
    <div class="flex gap-4">
      <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M7.08 6.06A9 9 0 1 0 15.94 17.94M7.08 6.06l.463 2.137a11.218 11.218 0 0 1 .473 2.104"/></svg>
      <div>
        <h3 class="font-bold text-amber-900 text-lg mb-2">🙏 Permohonan Maaf</h3>
        <p class="text-amber-800 mb-2">
          Terjadi <strong>kesalahan sistem</strong> yang menyebabkan data registrasi Anda terhapus tanpa sengaja. 
          Kami sangat minta maaf atas ketidaknyamanan ini.
        </p>
        <p class="text-amber-800">
          <strong>Kabar baik:</strong> Pembayaran Anda telah terverifikasi dan masih valid. 
          Silakan lengkapi form berikut untuk menyelesaikan registrasi ulang Anda.
        </p>
      </div>
    </div>
  </div>

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
      <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('checkout.reregister.store', ['token' => $token]) }}" method="POST">
    @csrf
    
    <div class="grid lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-6">

        {{-- ── DATA PESERTA ── --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
          <h2 class="font-display font-bold text-lg text-slate-900 mb-4">Data Peserta</h2>
          <div class="grid sm:grid-cols-2 gap-4">

            {{-- Nama Lengkap (KTP) --}}
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap <span class="text-slate-400 font-normal text-xs">(sesuai KTP – untuk keperluan data)</span></label>
              <input type="text" name="nama_peserta" value="{{ old('nama_peserta') }}"
                     placeholder="Nama sesuai KTP / Paspor"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
            </div>

            {{-- No. KTP --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">No. KTP / Identitas <span class="text-slate-400 font-normal text-xs">(16 digit)</span></label>
              <input type="text" name="no_ktp" value="{{ old('no_ktp') }}"
                     placeholder="16 digit KTP" maxlength="16" pattern="\d{16}"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors font-mono" required>
            </div>

            {{-- NICKNAME / BIB --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">
                Nickname
                <span class="text-slate-400 font-normal text-xs">(akan dicetak di BIB nomor dada)</span>
              </label>
              <input type="text" name="nickname" value="{{ old('nickname') }}"
                     placeholder="Cth: SpeedDemon, Pak Budi, RunnerGirl"
                     maxlength="30"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
              <p class="text-xs text-slate-400 mt-1">Bebas diisi apa saja, boleh sama dengan orang lain. Maks. 30 karakter.</p>
            </div>

            {{-- Event selection --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Event</label>
              <select id="event_id" name="event_id" required
                      class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors">
                <option value="">Pilih event</option>
                @foreach($events as $event)
                  <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>
                    {{ $event->title }} — {{ $event->date->format('d M Y') }}
                  </option>
                @endforeach
              </select>
            </div>

            {{-- Category selection --}}
            @php
              $selectedEvent = $events->firstWhere('id', old('event_id')) ?? $events->first();
            @endphp
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori Event</label>
              <select id="event_category_id" name="event_category_id" required
                      class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors">
                <option value="">Pilih kategori</option>
                @if($selectedEvent)
                  @foreach($selectedEvent->categories as $category)
                    <option value="{{ $category->id }}" @selected(old('event_category_id') == $category->id)>
                      {{ $category->name }} — Rp {{ number_format($category->active_price, 0, ',', '.') }}
                    </option>
                  @endforeach
                @endif
              </select>
            </div>

            {{-- Email --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
              <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="nama@email.com"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
              <p class="text-xs text-slate-400 mt-1">Email terbatas hanya untuk {{ $email }}</p>
            </div>

            {{-- No. HP --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">No. HP (WhatsApp)</label>
              <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
            </div>

            {{-- Tgl Lahir --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
            </div>

            {{-- Jenis Kelamin --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Kelamin</label>
              <select name="jenis_kelamin" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
                <option value="">Pilih</option>
                <option value="L" @selected(old('jenis_kelamin')=='L')>Laki-laki</option>
                <option value="P" @selected(old('jenis_kelamin')=='P')>Perempuan</option>
              </select>
            </div>

            {{-- Golongan Darah --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Golongan Darah</label>
              <select name="golongan_darah" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
                <option value="">Pilih</option>
                @foreach(['A','B','AB','O','A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gd)
                  <option value="{{ $gd }}" @selected(old('golongan_darah')==$gd)>{{ $gd }}</option>
                @endforeach
              </select>
            </div>

            {{-- Ukuran Kaos --}}
            <div class="sm:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Ukuran Kaos</label>
              <div class="flex flex-wrap gap-2 mb-2">
                @php
                  $sizeOptions = ['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'];
                @endphp
                @foreach($sizeOptions as $size)
                  @if($size === 'XXS')
                    <label class="cursor-pointer">
                      <input type="radio" name="ukuran_kaos" value="{{ $size }}" class="sr-only peer" {{ old('ukuran_kaos') == $size ? 'checked' : '' }} required>
                      <span class="block px-3 py-2 border-2 border-gray-200 rounded-lg text-xs font-semibold text-slate-600 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 hover:border-gray-300 transition-colors">{{ $size }}</span>
                    </label>
                  @else
                    <label class="cursor-pointer">
                      <input type="radio" name="ukuran_kaos" value="{{ $size }}-sport" class="sr-only peer" {{ old('ukuran_kaos') == "{$size}-sport" ? 'checked' : '' }} required>
                      <span class="block px-3 py-2 border-2 border-gray-200 rounded-lg text-xs font-semibold text-slate-600 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 hover:border-gray-300 transition-colors">{{ $size }}<span class="hidden sm:inline text-gray-400"> Sport</span></span>
                    </label>
                  @endif
                @endforeach
              </div>
              <p class="text-xs text-slate-500 mb-2"><span class="text-blue-600 font-medium">Sport</span> lebih kecil selisih 1 ukuran</p>
            </div>
          </div>
        </div>

        {{-- ── KONTAK DARURAT ── --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
          <h2 class="font-display font-bold text-lg text-slate-900 mb-4">Kontak Darurat</h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama</label>
              <input type="text" name="kontak_darurat_nama" value="{{ old('kontak_darurat_nama') }}" placeholder="Nama keluarga / teman dekat"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">No. HP</label>
              <input type="tel" name="kontak_darurat_hp" value="{{ old('kontak_darurat_hp') }}" placeholder="08xxxxxxxxxx"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
            </div>
          </div>
        </div>

      </div>

      {{-- ── SUMMARY ── --}}
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 sticky top-24">
          <h2 class="font-display font-bold text-lg text-slate-900 mb-4">Ringkasan</h2>
          
          <div class="mb-4 p-3 bg-green-50 rounded-lg border border-green-200">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
              <div>
                <p class="font-semibold text-green-700 text-sm">Pembayaran Terverifikasi</p>
                <p class="text-xs text-green-600 mt-1">Data pembayaran Anda sudah kami terima dan verifikasi. Cukup lengkapi data peserta dan selesai!</p>
              </div>
            </div>
          </div>

          <div class="mb-6 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <div>
                <p class="text-xs text-blue-700">
                  <strong>Link ini hanya berlaku sekali</strong> dan akan otomatis tidak aktif setelah digunakan. 
                  Pastikan data yang Anda masukkan sudah benar.
                </p>
              </div>
            </div>
          </div>

          <button type="submit" class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors active:scale-95">
            Selesaikan Registrasi
          </button>
          <p class="text-xs text-slate-400 text-center mt-3">Dengan klik ini, data registrasi Anda telah dikonfirmasi</p>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  const eventData = @json($eventData);

  const eventSelect = document.getElementById('event_id');
  const categorySelect = document.getElementById('event_category_id');

  function updateCategoryOptions() {
    const eventId = Number(eventSelect.value);
    categorySelect.innerHTML = '<option value="">Pilih kategori</option>';

    if (!eventId) {
      return;
    }

    const event = eventData.find(e => e.id === eventId);
    if (!event) {
      return;
    }

    event.categories.forEach(category => {
      const option = document.createElement('option');
      option.value = category.id;
      option.textContent = `${category.name} — Rp ${category.price}`;
      categorySelect.appendChild(option);
    });
  }

  eventSelect?.addEventListener('change', updateCategoryOptions);
</script>
@endsection
