@extends('layouts.admin')

@section('title', 'Edit Payment Method')

@section('admin-content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('admin.payment-methods.index') }}" class="text-blue-600 hover:text-blue-900 font-medium">← Back to Payment Methods</a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h1 class="text-2xl font-bold text-slate-900 mb-6">Edit Payment Method: {{ $paymentMethod->name }}</h1>

        @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.payment-methods.update', $paymentMethod) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Payment Method Name *</label>
                <input type="text" name="name" value="{{ old('name', $paymentMethod->name) }}" placeholder="e.g., Dana, OVO, QRIS" required
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none @error('name') border-red-500 @enderror">
                @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Description *</label>
                <textarea name="description" rows="3" placeholder="e.g., Dompet digital yang aman dan mudah" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none @error('description') border-red-500 @enderror">{{ old('description', $paymentMethod->description) }}</textarea>
                @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Instruksi pembayaran *</label>
                <textarea name="placeholder" rows="2" placeholder="e.g., Contoh nomor Dana: 08xx xxxx xxxx" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none @error('placeholder') border-red-500 @enderror">{{ old('placeholder', $paymentMethod->placeholder) }}</textarea>
                @error('placeholder')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Display Number (Optional)</label>
                <input type="text" name="display_number" value="{{ old('display_number', $paymentMethod->display_number) }}" placeholder="e.g., 0812 3456 7890"
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none @error('display_number') border-red-500 @enderror">
                @error('display_number')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                @if ($paymentMethod->name === 'QRIS')
                <label class="block text-sm font-medium text-slate-700 mb-2">Kode QR QRIS</label>
                @else
                <label class="block text-sm font-medium text-slate-700 mb-2">Icon (Optional)</label>
                @endif
                @if($paymentMethod->icon)
                <div class="mb-3">
                    @if ($paymentMethod->name === 'QRIS')
                    <p class="text-sm text-slate-600 mb-2">Kode QR:</p>
                    @else
                    <p class="text-sm text-slate-600 mb-2">Icon saat ini:</p>
                    @endif
                    <img src="{{ asset('storage/' . $paymentMethod->icon) }}" alt="{{ $paymentMethod->name }}" class="w-full h-full rounded">
                </div>
                @endif
                <input type="file" name="icon" accept="image/*" class="w-full px-4 py-2 border border-gray-200 rounded-lg @error('icon') border-red-500 @enderror">
                @if ($paymentMethod->name === 'QRIS')
                <p class="text-xs text-slate-500 mt-1">Format yang diterima: JPEG, PNG, JPG</p>
                @else
                <p class="text-xs text-slate-500 mt-1">Format yang diterima: JPEG, PNG, JPG, GIF, SVG (Max 2MB). Jangan diisi jika tidak ingin mengganti icon.</p>
                @endif
                @error('icon')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            @if ($paymentMethod->name !== 'QRIS')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Display Order *</label>
                <input type="number" name="display_order" value="{{ old('display_order', $paymentMethod->display_order) }}" min="0" required
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none @error('display_order') border-red-500 @enderror">
                <p class="text-xs text-slate-500 mt-1">Lower numbers appear first</p>
                @error('display_order')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
           

            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $paymentMethod->is_active) ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-medium text-slate-700">Active</span>
                </label>
            </div>
            @else
            <input type="hidden" name="is_active" value="{{ old('is_active', $paymentMethod->is_active ? 1 : 0) }}">
            <input type="hidden" name="display_order" value="{{ old('display_order', $paymentMethod->display_order ?? 0) }}">
            @endif

            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Update Payment Method
                </button>
                <a href="{{ route('admin.payment-methods.index') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-slate-900 font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
