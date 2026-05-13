@extends('layouts.admin')
@section('title','Detail Racepack')
@section('admin-content')

<div class="mb-8">
    <a href="{{ route('admin.racepack.monitor') }}" class="text-sm text-slate-600 hover:text-slate-900">← Kembali ke monitoring</a>
    <h1 class="font-display font-bold text-3xl text-slate-900 mt-3">Detail Pengambilan Racepack</h1>
    <p class="text-slate-600">Periksa detail peserta dan konfirmasi jika racepack sudah diambil.</p>
</div>

@if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
@endif

<div class="bg-white rounded-2xl border border-gray-200 p-6">
    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">Nama Peserta</p>
            <p class="mt-2 font-semibold text-slate-900">{{ $registration->nama_peserta }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">Ukuran Kaos</p>
            <p class="mt-2 font-semibold text-slate-900">{{ $registration->ukuran_kaos }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">Nomor BIB</p>
            <p class="mt-2 font-semibold text-slate-900">{{ $bibNumber }}</p>

        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">Event</p>
            <p class="mt-2 font-semibold text-slate-900">{{ $registration->event?->title ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">Kategori</p>
            <p class="mt-2 font-semibold text-slate-900">{{ $registration->category?->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">Email</p>
            <p class="mt-2 font-semibold text-slate-900">{{ $registration->email }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">No. HP</p>
            <p class="mt-2 font-semibold text-slate-900">{{ $registration->phone }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wide text-slate-500">Kontak Darurat</p>
            <p class="mt-2 font-semibold text-slate-900">{{ $registration->kontak_darurat_nama }} — {{ $registration->kontak_darurat_hp }}</p>
        </div>
    </div>

    <div class="mt-8 rounded-2xl border border-gray-100 bg-slate-50 p-5">
        <p class="text-xs uppercase tracking-wide text-slate-500">Status Pengambilan</p>
        <p class="mt-2 text-lg font-semibold {{ $registration->is_taken ? 'text-green-600' : 'text-slate-900' }}">
            {{ $registration->is_taken ? 'Sudah diambil' : 'Belum diambil' }}
        </p>
    </div>

    <form method="POST" action="{{ route('admin.racepack.confirm', $registration->invoice_number) }}" class="mt-8">
        @csrf
        <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
            Konfirmasi Racepack Diambil
        </button>
    </form>
</div>

@endsection
