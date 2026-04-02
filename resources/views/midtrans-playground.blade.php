<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Midtrans Snap · Playground</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">

    @if($isProduction)
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0b0e14;
            --surface:   #13181f;
            --surface2:  #1a2030;
            --border:    #232c3d;
            --accent:    #00e5a0;
            --accent2:   #0077ff;
            --danger:    #ff4d6a;
            --warn:      #ffb347;
            --text:      #e2e8f0;
            --muted:     #5a6a85;
            --label:     #8899b0;
            --mono:      'JetBrains Mono', monospace;
            --display:   'Syne', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--mono);
            min-height: 100vh;
            padding: 0;
            overflow-x: hidden;
        }

        /* ── GRID BACKGROUND ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(0,229,160,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0,229,160,.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        .layout {
            position: relative;
            z-index: 1;
            max-width: 960px;
            margin: 0 auto;
            padding: 40px 24px 80px;
        }

        /* ── HEADER ── */
        header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 16px;
            flex-wrap: wrap;
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .brand-label {
            font-family: var(--mono);
            font-size: 11px;
            letter-spacing: .2em;
            text-transform: uppercase;
            color: var(--accent);
        }

        h1 {
            font-family: var(--display);
            font-size: clamp(26px, 5vw, 40px);
            font-weight: 800;
            line-height: 1;
            color: var(--text);
        }

        .env-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .env-badge.sandbox {
            background: rgba(255,179,71,.1);
            border: 1px solid rgba(255,179,71,.3);
            color: var(--warn);
        }

        .env-badge.production {
            background: rgba(255,77,106,.1);
            border: 1px solid rgba(255,77,106,.3);
            color: var(--danger);
        }

        .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 1.4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: .35; }
        }

        /* ── GRID ── */
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 640px) {
            .grid { grid-template-columns: 1fr; }
        }

        /* ── PANELS ── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            background: var(--surface2);
        }

        .panel-icon {
            font-size: 15px;
        }

        .panel-title {
            font-family: var(--display);
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .05em;
            color: var(--label);
            text-transform: uppercase;
        }

        .panel-body {
            padding: 24px 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* ── FIELDS ── */
        .field { display: flex; flex-direction: column; gap: 6px; }

        label {
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--label);
        }

        input {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: var(--mono);
            font-size: 14px;
            padding: 10px 14px;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            width: 100%;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0,229,160,.12);
        }

        input::placeholder { color: var(--muted); }

        .hint {
            font-size: 11px;
            color: var(--muted);
        }

        /* ── AMOUNT DISPLAY ── */
        .amount-preview {
            font-family: var(--display);
            font-size: 28px;
            font-weight: 800;
            color: var(--accent);
            letter-spacing: -1px;
            min-height: 36px;
            transition: opacity .2s;
        }

        .amount-preview.empty { opacity: .3; }

        /* ── PAY BUTTON ── */
        .btn-pay {
            width: 100%;
            padding: 16px;
            background: var(--accent);
            color: #0b0e14;
            border: none;
            border-radius: 10px;
            font-family: var(--display);
            font-size: 16px;
            font-weight: 800;
            letter-spacing: .05em;
            cursor: pointer;
            transition: transform .15s, box-shadow .2s, opacity .2s;
            position: relative;
            overflow: hidden;
            margin-top: 8px;
        }

        .btn-pay:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,229,160,.35);
        }

        .btn-pay:active:not(:disabled) { transform: translateY(0); }

        .btn-pay:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .btn-pay .spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(0,0,0,.2);
            border-top-color: #0b0e14;
            border-radius: 50%;
            animation: spin .7s linear infinite;
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
        }

        .btn-pay.loading .spinner { display: block; }

        @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

        /* ── LOG ── */
        .log-panel {
            grid-column: 1 / -1;
        }

        .log-body {
            padding: 0;
        }

        #log-output {
            font-family: var(--mono);
            font-size: 12.5px;
            line-height: 1.7;
            min-height: 160px;
            max-height: 340px;
            overflow-y: auto;
            padding: 20px;
            background: var(--bg);
        }

        .log-entry {
            display: flex;
            gap: 12px;
            padding: 5px 0;
            border-bottom: 1px solid rgba(255,255,255,.03);
            animation: fadeIn .25s ease;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; } }

        .log-time {
            color: var(--muted);
            flex-shrink: 0;
            user-select: none;
        }

        .log-msg { color: var(--text); word-break: break-all; }
        .log-msg.success { color: var(--accent); }
        .log-msg.error   { color: var(--danger); }
        .log-msg.warn    { color: var(--warn); }
        .log-msg.info    { color: var(--accent2); }

        .log-placeholder {
            color: var(--muted);
            font-style: italic;
        }

        /* ── RESULT CARD ── */
        #result-card {
            display: none;
            grid-column: 1 / -1;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            animation: fadeIn .35s ease;
        }

        .result-body {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .result-item { display: flex; flex-direction: column; gap: 4px; }
        .result-key  { font-size: 10px; letter-spacing: .15em; text-transform: uppercase; color: var(--muted); }
        .result-val  { font-size: 13px; color: var(--text); word-break: break-all; }
        .result-val.status-settle  { color: var(--accent); }
        .result-val.status-pending { color: var(--warn); }
        .result-val.status-deny    { color: var(--danger); }
    </style>
</head>
<body>
<div class="layout">

    <header>
        <div class="brand">
            <span class="brand-label">// Midtrans</span>
            <h1>Snap Playground</h1>
        </div>
        @if($isProduction)
            <span class="env-badge production"><span class="dot"></span> Production</span>
        @else
            <span class="env-badge sandbox"><span class="dot"></span> Sandbox</span>
        @endif
    </header>

    <div class="grid">

        {{-- ── ORDER DETAILS ── --}}
        <div class="panel">
            <div class="panel-header">
                <span class="panel-icon">🧾</span>
                <span class="panel-title">Order Details</span>
            </div>
            <div class="panel-body">
                <div class="field">
                    <label for="item_name">Item Name</label>
                    <input id="item_name" type="text" placeholder="e.g. Premium Subscription" value="Test Item">
                </div>
                <div class="field">
                    <label for="amount">Amount (IDR)</label>
                    <input id="amount" type="number" placeholder="50000" value="50000" min="1000" step="1000">
                    <span class="hint">Minimum Rp 1.000</span>
                </div>
                <div class="amount-preview" id="amount-preview">Rp 50.000</div>
            </div>
        </div>

        {{-- ── CUSTOMER INFO ── --}}
        <div class="panel">
            <div class="panel-header">
                <span class="panel-icon">👤</span>
                <span class="panel-title">Customer Info</span>
            </div>
            <div class="panel-body">
                <div class="field">
                    <label for="customer_name">Full Name</label>
                    <input id="customer_name" type="text" placeholder="Budi Santoso" value="Test User">
                </div>
                <div class="field">
                    <label for="customer_email">Email</label>
                    <input id="customer_email" type="email" placeholder="budi@email.com" value="test@example.com">
                </div>
                <div class="field">
                    <label for="customer_phone">Phone <span style="color:var(--muted)">(optional)</span></label>
                    <input id="customer_phone" type="text" placeholder="08123456789">
                </div>

                <button class="btn-pay" id="btn-pay" onclick="startPayment()">
                    ▶ Launch Snap Payment
                    <span class="spinner"></span>
                </button>
            </div>
        </div>

        {{-- ── RESULT ── --}}
        <div id="result-card">
            <div class="panel-header">
                <span class="panel-icon">✅</span>
                <span class="panel-title">Payment Result</span>
            </div>
            <div class="result-body" id="result-body"></div>
        </div>

        {{-- ── LOG ── --}}
        <div class="panel log-panel">
            <div class="panel-header">
                <span class="panel-icon">📡</span>
                <span class="panel-title">Activity Log</span>
                <button onclick="clearLog()" style="margin-left:auto;background:none;border:1px solid var(--border);color:var(--muted);border-radius:5px;padding:3px 10px;font-family:var(--mono);font-size:11px;cursor:pointer;">clear</button>
            </div>
            <div class="log-body">
                <div id="log-output">
                    <div class="log-placeholder">// Waiting for activity…</div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    /* ── AMOUNT PREVIEW ── */
    const amountInput   = document.getElementById('amount');
    const amountPreview = document.getElementById('amount-preview');

    function updatePreview() {
        const v = parseInt(amountInput.value);
        if (!v || v < 1) {
            amountPreview.textContent = 'Rp —';
            amountPreview.classList.add('empty');
        } else {
            amountPreview.textContent = 'Rp ' + v.toLocaleString('id-ID');
            amountPreview.classList.remove('empty');
        }
    }

    amountInput.addEventListener('input', updatePreview);
    updatePreview();

    /* ── LOG ── */
    function log(msg, type = '') {
        const out = document.getElementById('log-output');
        const placeholder = out.querySelector('.log-placeholder');
        if (placeholder) placeholder.remove();

        const time = new Date().toLocaleTimeString('id-ID', { hour12: false });
        const entry = document.createElement('div');
        entry.className = 'log-entry';
        entry.innerHTML = `<span class="log-time">[${time}]</span><span class="log-msg ${type}">${msg}</span>`;
        out.appendChild(entry);
        out.scrollTop = out.scrollHeight;
    }

    function clearLog() {
        document.getElementById('log-output').innerHTML = '<div class="log-placeholder">// Log cleared.</div>';
    }

    /* ── PAYMENT ── */
    async function startPayment() {
        const btn = document.getElementById('btn-pay');

        const payload = {
            amount:         amountInput.value,
            item_name:      document.getElementById('item_name').value,
            customer_name:  document.getElementById('customer_name').value,
            customer_email: document.getElementById('customer_email').value,
            customer_phone: document.getElementById('customer_phone').value,
        };

        // Basic client-side check
        if (!payload.amount || parseInt(payload.amount) < 1000) { log('Amount must be at least Rp 1.000', 'error'); return; }
        if (!payload.customer_name)  { log('Customer name is required', 'error'); return; }
        if (!payload.customer_email) { log('Customer email is required', 'error'); return; }

        btn.disabled = true;
        btn.classList.add('loading');
        log('Requesting Snap token from server…', 'info');

        try {
            const res = await fetch("{{ route('midtrans.playground.token') }}", {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (!res.ok || data.error) {
                throw new Error(data.error || 'Failed to get token');
            }

            log(`Token received for order: ${data.order_id}`, 'success');
            log('Launching Snap popup…', 'info');

            window.snap.pay(data.token, {
                onSuccess(result) {
                    log(`Payment SUCCESS — ${result.transaction_status}`, 'success');
                    showResult(result);
                },
                onPending(result) {
                    log(`Payment PENDING — ${result.payment_type ?? 'waiting'}`, 'warn');
                    showResult(result);
                },
                onError(result) {
                    log(`Payment ERROR — ${result.status_message}`, 'error');
                    showResult(result);
                },
                onClose() {
                    log('Snap popup closed by user.', 'warn');
                },
            });

        } catch (err) {
            log(`Error: ${err.message}`, 'error');
        } finally {
            btn.disabled = false;
            btn.classList.remove('loading');
        }
    }

    /* ── RESULT CARD ── */
    function showResult(r) {
        const card = document.getElementById('result-card');
        const body = document.getElementById('result-body');
        card.style.display = 'block';

        const fields = [
            ['Order ID',          r.order_id],
            ['Status',            r.transaction_status],
            ['Payment Type',      r.payment_type],
            ['Transaction ID',    r.transaction_id],
            ['Gross Amount',      r.gross_amount ? 'Rp ' + parseInt(r.gross_amount).toLocaleString('id-ID') : '—'],
            ['Transaction Time',  r.transaction_time],
            ['Fraud Status',      r.fraud_status],
            ['Status Code',       r.status_code],
        ];

        body.innerHTML = fields.map(([k, v]) => {
            let cls = '';
            if (k === 'Status') {
                if (['settlement','capture'].includes(v))  cls = 'status-settle';
                else if (v === 'pending')                  cls = 'status-pending';
                else if (['deny','cancel','expire'].includes(v)) cls = 'status-deny';
            }
            return `<div class="result-item">
                        <span class="result-key">${k}</span>
                        <span class="result-val ${cls}">${v ?? '—'}</span>
                    </div>`;
        }).join('');

        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
</script>
</body>
</html>