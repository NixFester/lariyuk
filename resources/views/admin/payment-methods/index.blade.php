@extends('layouts.admin')

@section('title', 'Payment Methods')

@section('admin-content')
<div class="max-w-6xl">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-slate-900">Payment Methods</h1>
        <a href="{{ route('admin.payment-methods.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
            + Add Payment Method
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
        {{ session('success') }}
    </div>
    @endif

    <div class="mb-6 bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-slate-900 mb-4">Nomor WhatsApp</h2>
        <form method="POST" action="{{ route('admin.payment-methods.whatsapp-number.update') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Nomor:</label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', config('app.whatsapp_number')) }}" placeholder="e.g., +6281234567890"
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none @error('whatsapp_number') border-red-500 @enderror">
                @error('whatsapp_number')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-slate-500 mt-1">
                    nomor ini digunakan untuk kontak support, kontak verifikasi dan email tiket<br>
                    This number is used for support contact, verification contact, and ticket emails.</p>
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                Save WhatsApp Number
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Icon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Display Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($methods as $method)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-slate-900">{{ $method->name }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ Str::limit($method->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($method->icon)
                                @if($method->name === 'QRIS')
                                <img src="{{ asset('Logo_QRIS.svg') }}" alt="{{ $method->name }}" class="w-10 h-10 object-contain">
                                @else
                                <img src="{{ asset('storage/' . $method->icon) }}" alt="{{ $method->name }}" class="w-8 h-8 rounded">
                                @endif
                            @else
                            <span class="text-xs text-slate-400">No icon</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                            {{ $method->display_number ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                            {{ $method->display_order }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($method->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <a href="{{ route('admin.payment-methods.edit', $method) }}" class="text-blue-600 hover:text-blue-900 font-medium">Edit</a>
                            @if($method->name !== 'QRIS')
                            <form method="POST" action="{{ route('admin.payment-methods.destroy', $method) }}" class="inline" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                            No payment methods found. <a href="{{ route('admin.payment-methods.create') }}" class="text-blue-600 hover:text-blue-900">Create one</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
