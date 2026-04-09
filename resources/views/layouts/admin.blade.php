<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>@yield('title','Dashboard') – Slawi Run Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script>tailwind.config={theme:{extend:{fontFamily:{display:['Space Grotesk','sans-serif'],sans:['Plus Jakarta Sans','sans-serif']},colors:{primary:{50:'#e6fff7',100:'#b3ffe0',500:'#00d47b',600:'#00a35f',700:'#007248'}}}}}</script>
  <link rel="icon" type="image/jpeg" href="/logo/logo.jpeg">
</head>
<body class="min-h-screen bg-slate-100" style="font-family:'Plus Jakarta Sans',sans-serif">
  <div class="flex h-screen overflow-hidden">

    {{-- Mobile sidebar overlay --}}
    <div id="sidebarOverlay" class="fixed inset-0 z-20 bg-black/40 hidden md:hidden"></div>

    {{-- Mobile Sidebar --}}
    <aside id="mobileSidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-white transform -translate-x-full transition-transform duration-200 ease-out md:hidden">
      <div class="flex items-center justify-between p-6 border-b border-slate-800">
        <div class="flex items-center gap-2">
          <img src="/logo/logo.jpeg" alt="Slawi Run" class="w-8 h-8 rounded-lg object-cover">
          <div>
            <span style="font-family:'Space Grotesk',sans-serif" class="font-bold text-green-400">Slawi Run</span>
            <p class="text-slate-400 text-xs">Admin Panel</p>
          </div>
        </div>
        <button id="mobileSidebarClose" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-300 hover:bg-slate-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/></svg>
          Dashboard
        </a>
        <a href="{{ route('admin.events.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.events.*') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Event
        </a>
        <a href="{{ route('admin.registrations.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.registrations.*') && !request()->routeIs('admin.registrations.verification') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Pendaftar
        </a>
        <a href="{{ route('admin.registrations.verification') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.registrations.verification') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors relative">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Verifikasi Pembayaran
          <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold bg-yellow-500 text-white rounded-full">
            @php
              $pendingCount = App\Models\Registration::where('payment_status', 'pending')->whereNotNull('whatsapp_confirmed_at')->count();
            @endphp
            {{ $pendingCount }}
          </span>
        </a>
        <a href="{{ route('admin.payment-methods.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.payment-methods.*') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
          Metode Pembayaran
        </a>
        <a href="{{ route('admin.apology-emails.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.apology-emails.*') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          Email Permohonan Maaf
        </a>
      </nav>

      <div class="p-4 border-t border-slate-800">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center text-xs font-bold">
            {{ substr(auth('admin')->user()->name, 0, 1) }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ auth('admin')->user()->name }}</p>
            <p class="text-xs text-slate-400 truncate">{{ auth('admin')->user()->email }}</p>
          </div>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
          @csrf
          <button type="submit" class="w-full text-left px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
            Keluar
          </button>
        </form>
        <a href="{{ route('home') }}" target="_blank" class="mt-1 block text-xs text-slate-500 hover:text-slate-300 px-3 py-1 transition-colors">
          Lihat Situs &rarr;
        </a>
      </div>
    </aside>

    {{-- Desktop Sidebar --}}
    <aside class="hidden md:flex w-64 bg-slate-900 text-white flex-col flex-shrink-0">
      <div class="p-6 border-b border-slate-800">
        <div class="flex items-center gap-2">
          <img src="/logo/logo.jpeg" alt="Slawi Run" class="w-8 h-8 rounded-lg object-cover">
          <div>
            <span style="font-family:'Space Grotesk',sans-serif" class="font-bold text-green-400">Slawi Run</span>
            <p class="text-slate-400 text-xs">Admin Panel</p>
          </div>
        </div>
      </div>

      <nav class="flex-1 p-4 space-y-1">
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 13a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/></svg>
          Dashboard
        </a>
        <a href="{{ route('admin.events.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.events.*') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          Event
        </a>
        <a href="{{ route('admin.registrations.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.registrations.*') && !request()->routeIs('admin.registrations.verification') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          Pendaftar
        </a>
        <a href="{{ route('admin.registrations.verification') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.registrations.verification') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors relative">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Verifikasi Pembayaran
          <span class="ml-auto inline-flex items-center justify-center px-2 py-0.5 text-xs font-semibold bg-yellow-500 text-white rounded-full">
            @php
              $pendingCount = App\Models\Registration::where('payment_status', 'pending')->whereNotNull('whatsapp_confirmed_at')->count();
            @endphp
            {{ $pendingCount }}
          </span>
        </a>
        <a href="{{ route('admin.payment-methods.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.payment-methods.*') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
          Metode Pembayaran
        </a>
        <a href="{{ route('admin.apology-emails.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.apology-emails.*') ? 'bg-green-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          Email Permohonan Maaf
        </a>
      </nav>

      <div class="p-4 border-t border-slate-800">
        <div class="flex items-center gap-3 mb-3">
          <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center text-xs font-bold">
            {{ substr(auth('admin')->user()->name, 0, 1) }}
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-white truncate">{{ auth('admin')->user()->name }}</p>
            <p class="text-xs text-slate-400 truncate">{{ auth('admin')->user()->email }}</p>
          </div>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
          @csrf
          <button type="submit" class="w-full text-left px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors">
            Keluar
          </button>
        </form>
        <a href="{{ route('home') }}" target="_blank" class="mt-1 block text-xs text-slate-500 hover:text-slate-300 px-3 py-1 transition-colors">
          Lihat Situs &rarr;
        </a>
      </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white border-b border-gray-200 px-4 py-4 flex items-center justify-between gap-4 md:px-6">
        <div class="flex items-center gap-3">
          <button id="mobileSidebarButton" class="md:hidden inline-flex items-center justify-center rounded-lg p-2 text-slate-600 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <h1 class="font-display font-bold text-xl text-slate-800">@yield('title','Dashboard')</h1>
        </div>
        <div class="text-sm text-slate-400">{{ now()->format('d M Y') }}</div>
      </header>

      <main class="flex-1 min-w-0 overflow-y-auto overflow-x-auto p-6">
        @if(session('success'))
          <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm mb-6">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm mb-6">{{ session('error') }}</div>
        @endif

        @yield('admin-content')
      </main>
    </div>
  </div>
  <script>
    const mobileSidebarButton = document.getElementById('mobileSidebarButton');
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileSidebarClose = document.getElementById('mobileSidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
      mobileSidebar.classList.remove('-translate-x-full');
      mobileSidebar.classList.add('translate-x-0');
      sidebarOverlay.classList.remove('hidden');
    }

    function closeSidebar() {
      mobileSidebar.classList.add('-translate-x-full');
      mobileSidebar.classList.remove('translate-x-0');
      sidebarOverlay.classList.add('hidden');
    }

    if (mobileSidebarButton) {
      mobileSidebarButton.addEventListener('click', openSidebar);
    }
    if (mobileSidebarClose) {
      mobileSidebarClose.addEventListener('click', closeSidebar);
    }
    if (sidebarOverlay) {
      sidebarOverlay.addEventListener('click', closeSidebar);
    }
  </script>
  @stack('scripts')
</body>
</html>
