<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Slawi Run') – Platform Tiket Event Lari</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
  <script>tailwind.config={theme:{extend:{fontFamily:{display:['Space Grotesk','sans-serif'],sans:['Plus Jakarta Sans','sans-serif']},colors:{primary:{50:'#e6fff7',100:'#b3ffe0',500:'#00d47b',600:'#00a35f',700:'#007248'},accent:{500:'#ff6b35',600:'#e55a2b'},surface:{50:'#f8fafb',100:'#f1f5f7',200:'#e3e9ed'}}}}}</script>
  <link rel="icon" type="image/jpeg" href="/logo/logo.jpeg">
  <style>
    body{font-family:'Plus Jakarta Sans',sans-serif;background:#f1f5f7;color:#0f1720}
    .font-display{font-family:'Space Grotesk',sans-serif}
    .card-hover{transition:transform .3s ease,box-shadow .3s ease}
    .card-hover:hover{transform:translateY(-4px);box-shadow:0 20px 40px -12px rgba(0,0,0,.12)}
    .btn-press:active{transform:scale(.97)}
    input:focus,select:focus,textarea:focus{outline:none;box-shadow:0 0 0 3px rgba(0,163,95,.2)}
    .pulse-dot{animation:pulse 2s ease-in-out infinite}
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
  </style>
  @stack('styles')
</head>
<body class="min-h-screen">
  <nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
          <img src="/logo/logo.jpeg" alt="Slawi Run" class="w-10 h-10 rounded-xl object-cover">
          <span class="font-display font-bold text-xl text-primary-700">Slawi Run</span>
        </a>
        <div class="hidden md:flex items-center gap-6">
          <a href="{{ route('home') }}" class="text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors">Beranda</a>
          <a href="{{ route('events.index') }}" class="text-sm font-medium text-slate-600 hover:text-primary-600 transition-colors">Event</a>
        </div>
        <div class="flex items-center gap-3">
          {{-- Notification Bell --}}
          <button id="notificationBell" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors hidden" onclick="openNotificationModal()">
            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span id="notificationCount" class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full hidden">
              <span id="notifyNumber">1</span>
            </span>
          </button>

          <a href="{{ route('admin.login') }}" class="hidden sm:inline-flex px-4 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded-lg transition-colors">Admin</a>
          <button id="mobileBtn" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
        </div>
      </div>
    </div>
    <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-gray-200 px-4 py-3 space-y-1">
      <a href="{{ route('home') }}" class="block px-3 py-2 text-sm text-slate-600 hover:bg-gray-50 rounded-lg">Beranda</a>
      <a href="{{ route('events.index') }}" class="block px-3 py-2 text-sm text-slate-600 hover:bg-gray-50 rounded-lg">Event</a>
      <a href="{{ route('admin.login') }}" class="block px-3 py-2 text-sm text-primary-600 hover:bg-primary-50 rounded-lg">Admin</a>
    </div>
  </nav>

  <main class="pt-16">
    @if(session('success'))
      <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
      </div>
    @endif
    @if(session('error'))
      <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
      </div>
    @endif
    @yield('content')
  </main>

  <footer class="bg-slate-900 text-white mt-16">
    <div class="max-w-7xl mx-auto px-4 py-12">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
        <div>
          <span class="font-display font-bold text-lg text-primary-400">Slawi Run</span>
          <p class="text-slate-400 text-sm mt-2">Platform terpercaya pendaftaran event lari di Indonesia.</p>
        </div>
        <div>
          <h3 class="font-semibold mb-3 text-slate-200">Event</h3>
          <ul class="space-y-2 text-sm text-slate-400">
            <li><a href="{{ route('events.index') }}" class="hover:text-white">Semua Event</a></li>
          </ul>
        </div>
        <div>
          <h3 class="font-semibold mb-3 text-slate-200">Bantuan</h3>
          <ul class="space-y-2 text-sm text-slate-400">
            <li><a href="#" class="hover:text-white">Syarat & Ketentuan</a></li>
            <li><a href="#" class="hover:text-white">Kebijakan Privasi</a></li>
          </ul>
        </div>
      </div>
      <div class="border-t border-slate-800 pt-8 text-center text-sm text-slate-400">&copy; {{ date('Y') }} Slawi Run. Hak cipta dilindungi.</div>
    </div>
  </footer>

  <script>
    document.getElementById('mobileBtn').addEventListener('click',()=>{document.getElementById('mobileMenu').classList.toggle('hidden')});

    // Notification System
    let gPayments = [];
    let gNotificationInterval = null;

    // Helper functions for managing payment list in localStorage
    function getPaymentsList() {
      const stored = localStorage.getItem('lariyuk_payments');
      if (stored) {
        try {
          return JSON.parse(stored);
        } catch (e) {
          console.log('Error parsing payments:', e);
          return [];
        }
      }
      return [];
    }

    function addPayment(registration) {
      let payments = getPaymentsList();
      // Check if this exact invoice already exists
      const invoiceExists = payments.some(p => p.invoice === registration.invoice_number);
      if (!invoiceExists) {
        // Only add if this invoice doesn't already exist
        payments.unshift({
          id: registration.id,
          invoice: registration.invoice_number,
          addedAt: new Date().toISOString(),
          status: registration.payment_status,
        });
        localStorage.setItem('lariyuk_payments', JSON.stringify(payments));
      }
      return payments;
    }

    function removePayment(invoice) {
      let payments = getPaymentsList();
      payments = payments.filter(p => p.invoice !== invoice);
      localStorage.setItem('lariyuk_payments', JSON.stringify(payments));
      return payments;
    }

    function clearAllPayments() {
      localStorage.removeItem('lariyuk_payments');
    }

    function initNotificationBell() {
      // First, check localStorage for cached payments
      gPayments = getPaymentsList();
      
      let queryParam = null;
      
      if (gPayments.length > 0) {
        queryParam = gPayments[0].invoice;
      } else {
        // Look for invoice-like format in URL (INV-*)
        const pathParts = window.location.pathname.split('/').filter(p => p);
        const lastPart = pathParts[pathParts.length - 1];
        if (lastPart && lastPart.includes('INV-')) {
          queryParam = lastPart;
        }
      }
      
      if (queryParam) {
        // Try to get registration from API using invoice
        fetch('/api/notifications?invoice=' + queryParam)
          .then(r => r.json())
          .then(data => {
            if (data.registration && data.registration.id) {
              // Add to payments list
              gPayments = addPayment(data.registration);
              showNotificationBell(data);
              
              // Clear old interval if exists
              if (gNotificationInterval) clearInterval(gNotificationInterval);
              
              // Poll every 10 seconds
              gNotificationInterval = setInterval(() => pollNotifications(), 10000);
            }
          })
          .catch(e => console.log('Notification init:', e));
      } else if (gPayments.length > 0) {
        // If no URL param but have stored payments, start polling immediately
        fetch('/api/notifications?invoice=' + gPayments[0].invoice)
          .then(r => r.json())
          .then(data => {
            if (data.registration) {
              showNotificationBell(data);
              
              // Clear old interval if exists
              if (gNotificationInterval) clearInterval(gNotificationInterval);
              
              // Poll every 10 seconds
              gNotificationInterval = setInterval(() => pollNotifications(), 10000);
            }
          })
          .catch(e => console.log('Notification init (from storage):', e));
      }
    }

    function pollNotifications() {
      gPayments = getPaymentsList();
      if (gPayments.length === 0) {
        return;
      }
      
      // Poll the first payment (most recent)
      const firstPayment = gPayments[0];
      if (firstPayment) {
        fetch('/api/notifications?invoice=' + firstPayment.invoice)
          .then(r => r.json())
          .then(data => showNotificationBell(data))
          .catch(e => console.log('Poll error:', e));
      }
    }

    function showNotificationBell(data) {
      const bell = document.getElementById('notificationBell');
      const count = document.getElementById('notificationCount');
      const countNum = document.getElementById('notifyNumber');
      
      if (data.count > 0) {
        bell.classList.remove('hidden');
        if (data.count > 0) {
          count.classList.remove('hidden');
          countNum.textContent = data.count;
        }
        
        // Store notifications for modal
        window.currentNotifications = data.notifications;
        window.currentRegistration = data.registration;
      }
    }

    function openNotificationModal() {
      const modal = document.getElementById('notificationModal');
      const content = document.getElementById('notificationContent');
      const paymentGateway = {!! json_encode(config('payment.gateway')) !!}.trim();
      gPayments = getPaymentsList();
      
      if (gPayments.length === 0) return;
      
      let html = '';
      
      // Show all stored payments
      gPayments.forEach((payment, idx) => {
        // Determine the correct URL based on global payment gateway setting
        let statusUrl = '/checkout/pending/' + payment.invoice;
        if (payment.status === 'paid') {
          statusUrl = '/checkout/success/' + payment.invoice;
        } else {
          if (paymentGateway === 'midtrans') {
            statusUrl = '/checkout/midtrans/initiate/' + payment.invoice;
          } else if (paymentGateway === 'ipaymu') {
            statusUrl = '/checkout/ipaymu/initiate/' + payment.invoice;
          }
        }
        console.log('Payment Gateway:', paymentGateway, 'URL:', statusUrl);
        
        html += `
          <div class="mb-4 p-4 rounded-lg border border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between mb-3">
              <div class="flex-1">
                <p class="text-xs text-slate-500 font-semibold">INVOICE</p>
                <p class="font-mono font-bold text-slate-800">${payment.invoice}</p>
              </div>
              <button onclick="deletePaymentFromList('${payment.invoice}')" class="px-3 py-1.5 text-xs font-semibold bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </div>
            <a href="${statusUrl}" class="inline-block px-3 py-1.5 text-xs font-semibold bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
              Lihat Status Pembayaran
            </a>
          </div>
        `;
      });
      
      content.innerHTML = html;
      modal.classList.remove('hidden');
    }

    function closeNotificationModal() {
      document.getElementById('notificationModal').classList.add('hidden');
    }

    function deletePaymentFromList(invoice) {
      const confirmed = confirm(
        `⚠️ Apakah Anda yakin ingin menghapus akses ke invoice ini?\n\nInvoice: ${invoice}\n\nAnda tidak akan bisa mengakses status pembayaran invoice ini lagi.`
      );
      
      if (confirmed) {
        removePayment(invoice);
        // Refresh the modal
        openNotificationModal();
      }
    }

    function clearNotificationData() {
      const confirmed = confirm('Apakah Anda yakin ingin menghapus SEMUA akses pendaftaran yang tersimpan?\n\nAnda tidak akan bisa mengakses status pembayaran lagi.');
      if (confirmed) {
        clearAllPayments();
        document.getElementById('notificationBell').classList.add('hidden');
        document.getElementById('notificationCount').classList.add('hidden');
        closeNotificationModal();
      }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', initNotificationBell);

    // Close notification modal when clicking outside
    document.getElementById('notificationModal')?.addEventListener('click', function(e) {
      if (e.target === this) closeNotificationModal();
    });
  </script>

  {{-- Notification Modal --}}
  <div id="notificationModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" onclick="closeNotificationModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-slate-900">Pendaftaran Tersimpan</h2>
        <button onclick="closeNotificationModal()" class="text-slate-400 hover:text-slate-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div id="notificationContent" class="max-h-96 overflow-y-auto mb-4">
        <!-- Payments will be inserted here -->
      </div>

      <div class="flex gap-2">
        <button onclick="closeNotificationModal()" class="flex-1 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition-colors text-sm">
          Tutup
        </button>
        <button onclick="clearNotificationData()" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-lg transition-colors text-sm" title="Hapus semua akses pendaftaran">
          Hapus Semua
        </button>
      </div>
    </div>
  </div>

  @stack('scripts')
</body>
</html>
