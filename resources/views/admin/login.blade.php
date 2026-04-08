<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Admin Login – LariYuk</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <script>tailwind.config={theme:{extend:{fontFamily:{display:['Space Grotesk','sans-serif'],sans:['Plus Jakarta Sans','sans-serif']},colors:{primary:{600:'#00a35f',700:'#007248',500:'#00d47b'}}}}}</script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4" style="font-family:'Plus Jakarta Sans',sans-serif">
  <div class="w-full max-w-md">
    <div class="text-center mb-8">
      <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-green-700 flex items-center justify-center mx-auto mb-4">
        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      </div>
      <h1 style="font-family:'Space Grotesk',sans-serif" class="text-2xl font-bold text-white">LariYuk Admin</h1>
      <p class="text-slate-400 text-sm mt-1">Masuk ke panel administrasi</p>
    </div>
    <div class="bg-white rounded-2xl p-8 shadow-2xl">
      @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-6">
          @foreach($errors->all() as $err)<p class="text-sm text-red-700">{{ $err }}</p>@endforeach
        </div>
      @endif
      <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
        @csrf
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Admin</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@lariyuk.id" required autofocus
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition-all">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
          <input type="password" name="password" placeholder="••••••••" required
                 class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition-all">
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded">
          <label for="remember" class="text-sm text-slate-600">Ingat saya</label>
        </div>
        <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors">
          Masuk ke Dashboard
        </button>
      </form>
      <p class="text-center text-xs text-slate-400 mt-6">Call Support / Developer <a href="https://elcoding.id" target="_blank" class="text-green-500 hover:underline">elcoding.id</a></p>
    </div>
    <p class="text-center mt-4"><a href="{{ route('home') }}" class="text-slate-400 hover:text-white text-sm">&larr; Kembali ke situs</a></p>
  </div>
</body>
</html>
