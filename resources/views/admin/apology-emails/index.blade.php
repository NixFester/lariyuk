@extends('layouts.app')
@section('title', 'Kelola Email Permohonan Maaf')
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <!-- Header -->
  <div class="mb-8">
    <h1 class="font-display text-3xl font-bold text-slate-900">📧 Email Permohonan Maaf</h1>
    <p class="text-slate-500 mt-2">Kelola token dan kirim email permohonan maaf ke peserta yang tuas due to system error</p>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-500">Total Token</p>
          <p class="text-2xl font-bold text-slate-900">{{ $stats['total_tokens'] }}</p>
        </div>
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773c.058.26.155.51.27.747l1.745.807a1 1 0 01.528 1.079L5.5 15.5a1 1 0 01-.96.753H2a1 1 0 01-1-1V3Z"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-500">Belum Dikirim</p>
          <p class="text-2xl font-bold text-orange-600">{{ $stats['pending_send'] }}</p>
        </div>
        <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-500">Sudah Dikirim</p>
          <p class="text-2xl font-bold text-green-600">{{ $stats['already_sent'] }}</p>
        </div>
        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-slate-500">Kadaluarsa</p>
          <p class="text-2xl font-bold text-red-600">{{ $stats['expired'] }}</p>
        </div>
        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Action Buttons -->
  @if($stats['pending_send'] > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="font-semibold text-blue-900 mb-2">Siap Mengirim Email</h3>
          <p class="text-sm text-blue-700 mb-4">Anda memiliki {{ $stats['pending_send'] }} email yang siap dikirim ke peserta.</p>
          <button onclick="sendAllEmails()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
            📬 Kirim {{ $stats['pending_send'] }} Email
          </button>
        </div>
      </div>
    </div>
  @endif

  @if($stats['expired'] > 0)
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
      <div class="flex items-start gap-4">
        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="font-semibold text-red-900 mb-2">Ada Token Kadaluarsa</h3>
          <p class="text-sm text-red-700 mb-4">{{ $stats['expired'] }} token sudah kadaluarsa dan tidak bisa digunakan lagi.</p>
          <button onclick="regenerateExpired()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
            🔄 Regen Token Kadaluarsa
          </button>
        </div>
      </div>
    </div>
  @endif

  <!-- Tokens Table -->
  <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-slate-900">Daftar Token Belum Dikirim</h2>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-3 text-left font-semibold text-slate-700">Email</th>
            <th class="px-6 py-3 text-left font-semibold text-slate-700">Token</th>
            <th class="px-6 py-3 text-left font-semibold text-slate-700">Dibuat</th>
            <th class="px-6 py-3 text-left font-semibold text-slate-700">Kadaluarsa</th>
            <th class="px-6 py-3 text-left font-semibold text-slate-700">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          @forelse($tokens as $token)
            <tr class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $token->email }}</span>
              </td>
              <td class="px-6 py-4">
                <span class="font-mono text-xs text-slate-500">{{ substr($token->token, 0, 8) }}...</span>
              </td>
              <td class="px-6 py-4 text-xs text-slate-500">
                {{ $token->created_at->format('d M Y H:i') }}
              </td>
              <td class="px-6 py-4 text-xs">
                <span class="px-2 py-1 rounded-full text-xs font-medium
                  @if($token->expires_at->isFuture())
                    bg-green-100 text-green-700
                  @else
                    bg-red-100 text-red-700
                  @endif">
                  {{ $token->expires_at->format('d M Y') }}
                </span>
              </td>
              <td class="px-6 py-4">
                <form action="{{ route('admin.apology-emails.send-one', $token->id) }}" method="POST" class="inline">
                  @csrf
                  <button class="text-blue-600 hover:text-blue-700 font-medium text-xs">Kirim Tes</button>
                </form>
                |
                <a href="{{ route('admin.apology-emails.show', $token->id) }}" class="text-slate-600 hover:text-slate-700 font-medium text-xs">Lihat</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                ✅ Semua email sudah dikirim!
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($tokens->hasPages())
      <div class="px-6 py-4 border-t border-gray-200">
        {{ $tokens->links() }}
      </div>
    @endif
  </div>
</div>

<script>
async function sendAllEmails() {
  if (!confirm('Kirim {{ $stats["pending_send"] }} email permohonan maaf?\n\nProses ini tidak bisa dibatalkan.')) {
    return;
  }

  const btn = event.target;
  btn.disabled = true;
  btn.textContent = '⏳ Mengirim...';

  try {
    const response = await fetch('{{ route("admin.apology-emails.send-all") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();

    if (data.success) {
      alert(`✅ Email Terkirim!\n\nBerhasil: ${data.sent}\nGagal: ${data.failed}\n\nTotal: ${data.total}`);
      location.reload();
    } else {
      alert('❌ ' + data.message);
    }
  } catch (error) {
    alert('❌ Error: ' + error.message);
  } finally {
    btn.disabled = false;
    btn.textContent = '📬 Kirim {{ $stats["pending_send"] }} Email';
  }
}

async function regenerateExpired() {
  if (!confirm('Regenerasi {{ $stats["expired"] }} token yang sudah kadaluarsa?\n\nToken baru akan berlaku 7 hari.')) {
    return;
  }

  try {
    const response = await fetch('{{ route("admin.apology-emails.regenerate-expired") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Content-Type': 'application/json'
      }
    });

    const data = await response.json();

    if (data.success) {
      alert(`✅ Token Berhasil Diregen!\n\nToken yang diregen: ${data.regenerated}`);
      location.reload();
    } else {
      alert('❌ ' + data.message);
    }
  } catch (error) {
    alert('❌ Error: ' + error.message);
  }
}
</script>

@if($errors->any())
  <script>
    window.addEventListener('load', function() {
      alert('❌ Error:\n\n' + @json($errors->all()).join('\n'));
    });
  </script>
@endif

@if(session('success'))
  <script>
    window.addEventListener('load', function() {
      alert('✅ ' + '{{ session("success") }}');
    });
  </script>
@endif

@endsection
