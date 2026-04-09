@extends('layouts.app')
@section('title', 'Token Detail – ' . $token->email)
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-6">
    <a href="{{ route('admin.apology-emails.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
  </div>

  <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
      <h1 class="text-2xl font-bold text-slate-900">📧 Detail Token</h1>
      <p class="text-slate-500 text-sm mt-1">{{ $token->email }}</p>
    </div>

    <div class="p-6 space-y-6">
      <!-- Status -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
        <div class="flex items-center gap-2">
          @if($token->used)
            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">✓ Sudah Digunakan</span>
            <span class="text-xs text-slate-500">{{ $token->used_at->format('d M Y H:i') }}</span>
          @elseif($token->expires_at && $token->expires_at->isPast())
            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">✗ Kadaluarsa</span>
            <span class="text-xs text-slate-500">{{ $token->expires_at->format('d M Y H:i') }}</span>
          @else
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">⏳ Aktif</span>
            <span class="text-xs text-slate-500">Berlaku sampai {{ $token->expires_at->format('d M Y H:i') }}</span>
          @endif
        </div>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Email Penerima</label>
        <div class="flex items-center gap-2">
          <span class="font-mono text-sm bg-gray-100 px-3 py-2 rounded flex-1">{{ $token->email }}</span>
          <button onclick="copyText('{{ $token->email }}')" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded font-medium text-sm transition">
            Salin
          </button>
        </div>
      </div>

      <!-- Token -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Token</label>
        <div class="flex items-center gap-2">
          <span class="font-mono text-sm bg-gray-100 px-3 py-2 rounded flex-1 break-all">{{ $token->token }}</span>
          <button onclick="copyText('{{ $token->token }}')" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded font-medium text-sm transition whitespace-nowrap">
            Salin
          </button>
        </div>
      </div>

      <!-- Re-registration URL -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Link Re-registrasi</label>
        <div class="flex items-center gap-2">
          <span class="font-mono text-sm bg-blue-50 px-3 py-2 rounded flex-1 break-all text-blue-700">{{ $token->url }}</span>
          <button onclick="copyText('{{ $token->url }}')" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded font-medium text-sm transition whitespace-nowrap">
            Salin URL
          </button>
        </div>
        <p class="text-xs text-slate-500 mt-2">Peserta akan menggunakan link ini untuk re-registrasi</p>
      </div>

      <!-- Timestamps -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Dibuat</label>
          <div class="font-mono text-sm text-slate-600">{{ $token->created_at->format('d M Y H:i:s') }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Kadaluarsa</label>
          <div class="font-mono text-sm text-slate-600">{{ $token->expires_at->format('d M Y H:i:s') }}</div>
        </div>
      </div>

      @if($token->used_at)
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">Digunakan (Re-registration Selesai)</label>
          <div class="font-mono text-sm text-slate-600">{{ $token->used_at->format('d M Y H:i:s') }}</div>
        </div>
      @endif

      <!-- Actions -->
      @unless($token->used || ($token->expires_at && $token->expires_at->isPast()))
        <div class="pt-4 border-t border-gray-200">
          <h3 class="font-semibold text-slate-900 mb-3">Aksi</h3>
          <form action="{{ route('admin.apology-emails.send-one', $token->id) }}" method="POST">
            @csrf
            <button type="submit" onclick="return confirm('Kirim email ke {{ $token->email }}?')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
              📬 Kirim Email Test
            </button>
          </form>
          <p class="text-xs text-slate-500 mt-2">Gunakan untuk menguji atau mengirim ulang email ke peserta</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Preview Email -->
  <div class="mt-8 bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-slate-900">Preview Email</h2>
    </div>
    <div class="p-6 bg-gray-50 font-sans text-sm leading-relaxed">
      <div style="background: white; padding: 20px; border-radius: 8px;">
        <p><strong>To:</strong> {{ $token->email }}</p>
        <p><strong>Subject:</strong> 🙏 Permohonan Maaf - Sistem Error Registrasi</p>
        <hr style="margin: 20px 0;">
        <p>Halo,</p>
        <p>Kami dengan sangat menyesal harus memberitahukan bahwa terjadi <strong>kesalahan sistem</strong>...</p>
        <p style="margin: 20px 0;">
          <a href="{{ $token->url }}" style="display: inline-block; background-color: #10b981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px;">
            Klik di sini untuk Re-register
          </a>
        </p>
        <p>{{ $token->url }}</p>
      </div>
    </div>
  </div>
</div>

<script>
function copyText(text) {
  navigator.clipboard.writeText(text).then(() => {
    alert('✓ Copied to clipboard');
  });
}
</script>
@endsection
