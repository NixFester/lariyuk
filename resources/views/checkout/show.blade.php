@extends('layouts.app')
@section('title', 'Checkout – ' . $event->title)
@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="flex items-center gap-4 mb-8">
    <a href="{{ route('events.show', $event->slug) }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
      <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
      <h1 class="font-display text-2xl font-bold text-slate-900">Checkout</h1>
      <p class="text-sm text-slate-500">{{ $event->title }} – {{ $category->name }}</p>
    </div>
  </div>

  @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
      <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  @if(!$category->hasAvailableSlots())
    <div class="bg-orange-50 border border-orange-300 rounded-xl p-4 mb-6 flex items-center gap-3">
      <svg class="w-5 h-5 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M7.08 6.06A9 9 0 1 0 15.94 17.94M7.08 6.06l.463 2.137a11.218 11.218 0 0 1 .473 2.104"/></svg>
      <div>
        <p class="font-semibold text-orange-900">Kuota Kategori Penuh</p>
        <p class="text-sm text-orange-700">Maaf, kuota untuk kategori "{{ $category->name }}" telah penuh. Silakan </p>
        <a href="{{ route('events.show', $event->slug) }}" class="text-orange-600 hover:text-orange-700 font-medium underline">kembali dan pilih kategori lain</a>
      </div>
    </div>
  @endif

  <form action="{{ route('checkout.store') }}" method="POST">
    @csrf
    <input type="hidden" name="event_id"          value="{{ $event->id }}">
    <input type="hidden" name="event_category_id" value="{{ $category->id }}">

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

            {{-- NICKNAME / BIB ── kunci baru ── --}}
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

            {{-- Email --}}
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
              <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
                     class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:border-green-500 focus:outline-none transition-colors" required>
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
              <details class="text-xs sm:text-sm text-slate-500 mt-1">
                <summary class="cursor-pointer hover:text-green-600 font-medium">📏 Lihat panduan ukuran kaos</summary>
                <div class="mt-3 rounded-lg border border-gray-200 overflow-hidden">
                  {{-- Mobile: Card Layout --}}
                  <div class="sm:hidden space-y-2 p-4 bg-white">
                    @foreach($shirtSizes as $size => $variants)
                      @foreach($variants as $variant => $dims)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                          <span class="font-bold text-green-600 text-base">{{ $size }}<span class="text-xs text-gray-500"> {{ ucfirst($variant) }}</span></span>
                          <div class="text-right text-xs sm:text-sm">
                            <p class="text-slate-600">Lebar: <span class="font-medium">{{ $dims['width'] }} cm</span></p>
                            <p class="text-slate-600">Panjang: <span class="font-medium">{{ $dims['length'] }} cm</span></p>
                          </div>
                        </div>
                      @endforeach
                    @endforeach
                  </div>
                  {{-- Desktop: Table Layout --}}
                  <div class="hidden sm:block overflow-x-auto">
                    <table class="text-sm w-full">
                      <thead><tr class="bg-gray-50"><th class="px-4 py-2 text-left font-semibold text-slate-700">Ukuran</th><th class="px-4 py-2 text-left font-semibold text-slate-700">Varian</th><th class="px-4 py-2 text-left font-semibold text-slate-700">Lebar</th><th class="px-4 py-2 text-left font-semibold text-slate-700">Panjang</th></tr></thead>
                      <tbody>
                        @foreach($shirtSizes as $size => $variants)
                          @foreach($variants as $variant => $dims)
                            <tr class="border-t border-gray-100"><td class="px-4 py-2 font-bold text-green-600">{{ $size }}</td><td class="px-4 py-2 text-slate-600">{{ ucfirst($variant) }}</td><td class="px-4 py-2">{{ $dims['width'] }} cm</td><td class="px-4 py-2">{{ $dims['length'] }} cm</td></tr>
                          @endforeach
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </details>
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

      {{-- ── ORDER SUMMARY ── --}}
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-200 p-6 sticky top-24">
          <h2 class="font-display font-bold text-lg text-slate-900 mb-4">Ringkasan Pesanan</h2>
          <div class="flex gap-4 mb-4 pb-4 border-b border-gray-100">
            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-20 h-20 object-cover rounded-lg flex-shrink-0">
            <div class="min-w-0">
              <h3 class="font-semibold text-slate-900 text-sm">{{ $event->title }}</h3>
              <p class="text-xs text-slate-500 mt-0.5">{{ $event->date->translatedFormat('d M Y') }}</p>
              <p class="text-xs text-green-600 font-medium mt-0.5">{{ $category->name }}</p>
            </div>
          </div>
          <div class="space-y-2 mb-4 pb-4 border-b border-gray-100 text-sm">
            <div class="flex justify-between">
              <span class="text-slate-500">Harga tiket</span>
              @if($isEarlyBird && $category->early_bird_price)
                <div class="text-right">
                  <span class="line-through text-slate-400 text-xs">Rp {{ number_format($category->normal_price,0,',','.') }}</span><br>
                  <span class="text-yellow-600 font-semibold">Rp {{ number_format($category->early_bird_price,0,',','.') }}</span>
                </div>
              @else
                <span>Rp {{ number_format($category->normal_price,0,',','.') }}</span>
              @endif
            </div>
            @if($isEarlyBird && $category->early_bird_price)
              <div class="flex justify-between text-green-600 text-xs font-medium">
                <span>Diskon Early Bird (10%)</span>
                <span>−Rp {{ number_format($category->normal_price - $category->early_bird_price,0,',','.') }}</span>
              </div>
            @endif
            <div class="flex justify-between text-slate-500">
              <span>Biaya Admin</span><span>Rp 5.000</span>
            </div>
          </div>
          <div class="flex justify-between items-center mb-6">
            <span class="font-medium text-slate-700">Total Bayar</span>
            <span class="font-display font-bold text-xl text-green-600">Rp {{ number_format($total,0,',','.') }}</span>
          </div>

          {{-- Email notice --}}
          <div class="flex items-start gap-2 mb-4 p-3 bg-blue-50 rounded-lg">
            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="text-xs text-blue-700">Tiket akan dikirimkan ke email kamu setelah pembayaran berhasil.</p>
          </div>

          <button type="submit" class="w-full px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition-colors active:scale-95">
            Bayar & Dapatkan Tiket
          </button>
          <p class="text-xs text-slate-400 text-center mt-3">Dengan klik ini, kamu menyetujui Syarat & Ketentuan</p>
        </div>
      </div>
    </div>
  </form>
</div>
@endsection
