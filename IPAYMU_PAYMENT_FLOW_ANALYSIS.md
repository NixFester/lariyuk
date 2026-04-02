# iPAYMU Payment Integration - Complete Analysis

## 📋 Executive Summary

The LariYuk project has a **dual payment system** with the ability to switch between two gateways:
- **Classic**: Manual payment with admin verification (default)
- **IPaymu**: Automated virtual account payment with instant ticket delivery

**Current Configuration**: `.env` has `PAYMENT_GATEWAY=classic`, but iPAYMU is fully implemented and ready to use.

---

## 🔐 Current Configuration (.env Settings)

```env
# Payment Gateway Setting
PAYMENT_GATEWAY=classic              # Switch to 'ipaymu' to enable automated payments

# IPaymu API Credentials (Sandbox)
IPAYMU_VA=1179001476652656
IPAYMU_API_KEY=5EE51567-B651-4BCF-8D82-BE4E802A3122
IPAYMU_BASE_URL=https://sandbox.ipaymu.com/api/v2
```

**Payment Processing Configuration**:
- Timeout: 30 minutes
- Admin fee: 5,000 IDR (fixed)
- Email: Via SMTP (configured in MAIL_* variables)

---

## 📊 Payment Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      REGISTRATION FORM SUBMISSION                       │
│                   (Event Details + User Information)                    │
└────────────────────────────┬────────────────────────────────────────────┘
                             │
                    RegistrationController::store()
                             │
                ┌────────────┴────────────┐
                │                         │
         ┌──────▼──────┐          ┌──────▼──────┐
         │  CLASSIC    │          │   IPaymu    │
         │  mode       │          │   mode      │
         └──────┬──────┘          └──────┬──────┘
                │                         │
    Redirect to:                 Redirect to:
    checkout.pending       checkout.ipaymu.initiate
         │                         │
         │                 ┌───────▼────────┐
         │                 │  IPaymuService │
         │                 │  ::createCharge│
         │                 │  (API Call)    │
         │                 └───────┬────────┘
         │                         │
         │                    Returns:
         │              - Transaction ID
         │              - VA Number
         │              - Error (or null)
         │                         │
         │                 ┌───────▼────────────────┐
         │                 │  ipaymu-pending.blade  │
         │                 │  (Status Polling Loop) │
         │                 └───────┬────────────────┘
         │                      │
         │              Every 5 seconds:
         │              checkStatus() AJAX
         │                      │
         │     ┌────────────────┼────────────────┐
         │     │                │                │
    Payment    │          Payment        Payment
    Method     │          Verified       Timeout
    Display    │             │
         │     │             │
         │     │      ┌──────▼──────────┐
         │     │      │ Send Ticket     │
         │     │      │ Email + Update  │
         │     │      │ payment_status  │
         │     │      │ to 'paid'       │
         │     │      └──────┬──────────┘
         │     │             │
    User       │        ┌────▼──────────┐
    Confirms   │        │ Redirect to   │
    Payment    │        │ checkout.     │
         │     │        │ success       │
         │     │        └──────┬────────┘
         │     │               │
         │     └───────────────┼──────────┐
         │                     │          │
         └─────────────────────┼──────────┘
                               │
                      ┌────────▼────────┐
                      │ SUCCESS PAGE    │
                      │ (Ticket Display)│
                      └─────────────────┘
```

---

## 🔄 Detailed Payment Flow (IPaymu Mode)

### **Phase 1: Registration & Charge Creation**

**File**: [app/Http/Controllers/RegistrationController.php](app/Http/Controllers/RegistrationController.php#L43-L142)

```php
// Step 1: User submits registration form
public function store(Request $request)
{
    // Validates: KTP, name, email, phone, date, size, emergency contact, etc.
    $registration = Registration::create([
        'invoice_number' => Registration::generateInvoice(),
        'payment_status' => 'pending',
        'subtotal' => $price,
        'admin_fee' => 5000,
        'total' => $price + 5000,
    ]);

    // Step 2: Route to payment gateway (based on PAYMENT_GATEWAY config)
    if (config('payment.gateway') === 'ipaymu') {
        return redirect()->route('checkout.ipaymu.initiate', $invoice);
    }
    return redirect()->route('checkout.pending', $invoice);
}
```

**Payment Data Collected**:
- Invoice Number (e.g., `INV-20260402-ABC123`)
- User Name, Email, Phone
- Total Amount (price + 5,000 admin fee)
- Event Details (for description)

---

### **Phase 2: IPaymu Charge Initiation**

**File**: [app/Http/Controllers/IPaymuController.php](app/Http/Controllers/IPaymuController.php#L17-L66)

```php
public function initiate(string $invoice)
{
    $registration = Registration::with(['event', 'category'])
        ->where('invoice_number', $invoice)->firstOrFail();

    // Prepare charge data
    $chargeData = [
        'name' => $registration->nama_peserta,
        'email' => $registration->email,
        'phone' => $registration->phone,
        'amount' => $registration->total,
        'invoice' => $registration->invoice_number,
        'description' => 'Event Registration - ' . $registration->event->name,
    ];

    // Call IPaymu Service
    $response = $this->ipaymu->createCharge($chargeData);

    if (!$response) {
        return redirect()->route('checkout.pending', $invoice)
            ->with('error', 'Failed to create transaction');
    }

    // Store transaction ID
    $registration->update([
        'ipaymu_transaction_id' => $response['data']['id'],
    ]);

    return view('checkout.ipaymu-pending', compact('registration'));
}
```

---

### **Phase 3: IPaymu Service - Charge Creation**

**File**: [app/Services/IPaymu/IPaymuService.php](app/Services/IPaymu/IPaymuService.php#L35-L145)

The service handles:
1. **Configuration validation** - Checks VA and API key are set
2. **Phone formatting** - Converts to international format (62XXXXXXXXXX)
3. **API request building** - Constructs directPayment parameters
4. **Error handling** - Catches and logs API errors

```php
public function createCharge(array $data): ?array
{
    // 1. Validate credentials
    if (empty($this->va) || empty($this->apiKey)) {
        throw new \Exception('IPaymu VA or API Key is not configured');
    }

    // 2. Set callback URLs (critical for webhook)
    $this->ipaymu->setURL([
        'ureturn' => route('checkout.success', $invoice),
        'unotify' => route('checkout.ipaymu.webhook'),
        'ucancel' => route('checkout.pending', $invoice),
    ]);

    // 3. Set buyer info
    $this->ipaymu->setBuyer([
        'name' => $name,
        'phone' => $this->formatPhone($phone),
        'email' => $email,
    ]);

    // 4. Build cart (required by IPaymu API)
    $cart = [
        'product' => [$data['description'] ?? 'Event Registration'],
        'quantity' => [1],
        'price' => [$amount],
        'description' => [$data['comments'] ?? 'Event ticket registration'],
        'weight' => [0], 'length' => [0], 'width' => [0], 'height' => [0],
    ];
    $this->ipaymu->addCart($cart);

    // 5. Create charge via directPayment
    $paymentData = [
        'amount' => (int) $amount,
        'paymentMethod' => 'va',              // Virtual Account
        'paymentChannel' => 'bca',            // Bank Central Asia
        'referenceId' => (string) $invoice,
        'expired' => 24,
        'expiredType' => 'hours',
    ];

    $response = $this->ipaymu->directPayment($paymentData);
    
    return $response;
}
```

**API Response Format** (on success):
```json
{
    "status": 200,
    "success": true,
    "data": {
        "id": "1234567890",           // Transaction ID
        "trx_id": "1234567890",
        "referenceId": "INV-20260402-ABC123",
        "va": "1179001476652656000001", // Virtual Account Number
        "amount": 500000,
        "expired": 86400,              // Expiration in seconds
        "status": "pending"
    }
}
```

---

### **Phase 4: Pending Payment Page (Status Polling)**

**File**: `resources/views/checkout/ipaymu-pending.blade.php` (View)

**JavaScript** automatically polls every 5 seconds:
```javascript
// AJAX call to check status
GET /checkout/ipaymu/check-status?invoice=INV-20260402-ABC123
```

---

### **Phase 5: Payment Status Checking (AJAX)**

**File**: [app/Http/Controllers/IPaymuController.php](app/Http/Controllers/IPaymuController.php#L68-L115)

```php
public function checkStatus(Request $request)
{
    $invoice = $request->query('invoice');
    $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

    // Check with IPaymu API
    $statusResponse = $this->ipaymu->checkStatus($registration->ipaymu_transaction_id);

    // Verify if payment is successful
    if ($this->ipaymu->isPaymentSuccessful($statusResponse)) {
        // UPDATE: Mark as paid
        $registration->update([
            'payment_status' => 'paid',
            'payment_verified_at' => now(),
            'ipaymu_paid_at' => now(),
        ]);

        // Send ticket email immediately
        Mail::to($registration->email)->send(new TicketMail($registration));
        $registration->update(['ticket_email_sent' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment verified! Your ticket has been sent to your email.',
            'isPaid' => true,
            'redirectTo' => route('checkout.success', $invoice),
        ]);
    }

    return response()->json([
        'status' => 'pending',
        'message' => 'Waiting for payment confirmation...',
        'isPaid' => false,
    ]);
}
```

---

### **Phase 6: Webhook Notification (Real-time)**

**File**: [app/Http/Controllers/IPaymuController.php](app/Http/Controllers/IPaymuController.php#L117-L166)

When payment is confirmed at bank, IPaymu POSTs to:
```
POST /checkout/ipaymu/webhook
Header: X-IPAYMU-SIGNATURE: <HMAC-SHA256 signature>
```

```php
public function webhook(Request $request)
{
    $payload = $request->all();
    $signature = $request->header('X-IPAYMU-SIGNATURE');

    // 1. Verify signature (prevents spoofed webhooks)
    if (!$this->ipaymu->verifySignature($payload, $signature)) {
        return response()->json(['status' => 'error'], 401);
    }

    // 2. Get reference ID from payload
    $referenceId = $payload['referenceId'] ?? null;
    $registration = Registration::where('invoice_number', $referenceId)->first();

    if (!$registration) {
        return response()->json(['status' => 'error'], 404);
    }

    // 3. Check if payment is successful
    if ($this->ipaymu->isPaymentSuccessful($payload)) {
        $registration->update([
            'payment_status' => 'paid',
            'payment_verified_at' => now(),
            'ipaymu_paid_at' => now(),
        ]);

        // Send ticket email
        Mail::to($registration->email)->send(new TicketMail($registration));

        return response()->json(['status' => 'success']);
    }

    return response()->json(['status' => 'pending']);
}
```

**Webhook Signature Verification**:
```php
public function verifySignature(array $data, string $signature): bool
{
    // Compute HMAC-SHA256 of JSON payload with API key
    $jsonPayload = json_encode($data, JSON_UNESCAPED_SLASHES);
    $computedSignature = hash_hmac('sha256', $jsonPayload, $this->apiKey, true);
    $computedSignatureb64 = base64_encode($computedSignature);

    // Timing-safe comparison
    return hash_equals($computedSignatureb64, $signature);
}
```

---

## 📋 Registration Model - Payment Fields

**File**: [app/Models/Registration.php](app/Models/Registration.php)

```php
protected $fillable = [
    // User Info
    'no_ktp', 'nama_peserta', 'email', 'phone',
    
    // Event Info
    'event_id', 'event_category_id',
    
    // Payment Fields
    'invoice_number',           // e.g., INV-20260402-ABC123
    'payment_method',           // 'va', 'qris', 'manual', etc.
    'payment_status',           // 'pending', 'paid', 'expired', 'failed'
    'subtotal',                 // Price of category
    'admin_fee',                // 5000
    'total',                    // subtotal + admin_fee
    
    // IPaymu Specific
    'ipaymu_transaction_id',    // Transaction ID from IPaymu API
    'ipaymu_paid_at',           // When IPaymu confirmed payment
    
    // Status Tracking
    'payment_verified_at',      // When verified (by admin or webhook)
    'ticket_email_sent',        // Whether ticket was sent
    'qris_displayed_at',        // Classic mode: when QRIS shown
    'whatsapp_confirmed_at',    // Classic mode: when user confirmed
];
```

---

## 🌐 Routes Configuration

**File**: [routes/web.php](routes/web.php#L32-L45)

```php
Route::prefix('checkout/ipaymu')->name('checkout.ipaymu.')->group(function () {
    // Initiate payment (creates charge at IPaymu)
    Route::get('/initiate/{invoice}',         [IPaymuController::class, 'initiate'])
        ->name('initiate');
    
    // Check payment status (AJAX polling)
    Route::get('/check-status',               [IPaymuController::class, 'checkStatus'])
        ->name('check-status');
    
    // Webhook from IPaymu (real-time payment notification)
    Route::post('/webhook',                   [IPaymuController::class, 'webhook'])
        ->name('webhook')
        ->withoutMiddleware(['web']);  // No CSRF for webhook
    
    // Test API credentials
    Route::get('/test',                       [IPaymuController::class, 'testPage'])
        ->name('test');
    Route::post('/test',                      [IPaymuController::class, 'runTest'])
        ->name('test.run');
});
```

---

## 💳 IPaymu Service - Complete Methods

**File**: [app/Services/IPaymu/IPaymuService.php](app/Services/IPaymu/IPaymuService.php)

### Main Methods:

| Method | Purpose | Returns |
|--------|---------|---------|
| `createCharge(array $data)` | Create direct payment charge | Response array \| null |
| `checkStatus(string $referenceId)` | Check transaction status | Status data \| null |
| `createRedirectPayment(array $data)` | Alternative redirect payment | Response array \| null |
| `checkBalance()` | Check merchant account balance | Balance data \| null |
| `verifySignature(array $data, string $sig)` | Verify webhook authenticity | bool |
| `isPaymentSuccessful(array $payload)` | Check if payment succeeded | bool |
| `formatPhone(string $phone)` | Convert phone to 62XXXXXXX format | string |
| `getTransactionIdFromResponse(array $resp)` | Extract transaction ID | string \| null |
| `getVANumberFromResponse(array $resp)` | Extract VA number | string \| null |
| `getErrorFromResponse(array $resp)` | Extract error message | string \| null |

### Success Status Indicators:
```php
$successStatuses = [
    'completed',   // Payment verified and completed
    'settled',     // Payment settled in merchant account
    'success',     // Payment successful
    'verified',    // Just verified
    '1',           // Numeric indicator
    1,
];
```

---

## 🧪 Testing the Integration

### 1. **Test API Credentials**
Navigate to: `http://localhost:8000/checkout/ipaymu/test`

This runs `IPaymuController::runTest()` which:
- Checks if credentials are loaded from .env
- Calls `checkBalance()` API
- Verifies API credentials are valid

### 2. **Test Payment Flow (Manual)**
1. Go to event registration page
2. Fill in all details and submit
3. You'll be redirected to IPaymu pending page
4. Wait for payment status check (automatic every 5 seconds)
5. Or manually visit: `GET /checkout/ipaymu/check-status?invoice=INV-20260402-ABC123`

### 3. **Test Webhook (with Postman)**
```
POST /checkout/ipaymu/webhook
Header: X-IPAYMU-SIGNATURE: <signature>
Body:
{
    "referenceId": "INV-20260402-ABC123",
    "status": "completed",
    "data": { "status": "completed" }
}
```

---

## 🔄 Switching Between Payment Gateways

### **Edit `.env`**:

```bash
# Enable IPaymu (automatic payment)
PAYMENT_GATEWAY=ipaymu

# Or switch back to manual verification
PAYMENT_GATEWAY=classic
```

**No database changes needed** - both gateways use the same Registration table:
- Classic mode uses: `whatsapp_confirmed_at` for user confirmation
- IPaymu uses: `ipaymu_transaction_id`, `ipaymu_paid_at` for tracking

---

## 📝 Key Configuration Files

### 1. **config/services.php**
```php
'ipaymu' => [
    'va' => env('IPAYMU_VA'),
    'api_key' => env('IPAYMU_API_KEY'),
    'base_url' => env('IPAYMU_BASE_URL', 'https://sandbox.ipaymu.com/api/v2'),
],
```

### 2. **config/payment.php**
```php
return [
    'gateway' => env('PAYMENT_GATEWAY', 'classic'),
    'ipaymu' => [
        'timeout' => 30 * 60,  // 30 minutes
    ],
];
```

### 3. **.env**
```env
PAYMENT_GATEWAY=classic                          # Current: manual
IPAYMU_VA=1179001476652656
IPAYMU_API_KEY=5EE51567-B651-4BCF-8D82-BE4E802A3122
IPAYMU_BASE_URL=https://sandbox.ipaymu.com/api/v2
```

---

## 🧠 Key Implementation Details

### **Why Two Payment Systems?**
1. **Classic (Manual)**: Admin reviews transfers and verifies manually
2. **IPaymu (Automated)**: Bank verifies, IPaymu notifies webhook, instant verification

### **Data Safety**
- Both flows use same `registrations` table
- No data loss when switching
- Each has unique fields:
  - Classic: `whatsapp_confirmed_at`, `qris_displayed_at`
  - IPaymu: `ipaymu_transaction_id`, `ipaymu_paid_at`

### **Ticket Email Delivery**
- **Classic**: Sent after admin verification (manual step in admin panel)
- **IPaymu**: Sent immediately when payment verified (either via polling or webhook)

### **Webhook Security**
- HMAC-SHA256 signature verification
- Timing-safe comparison (`hash_equals()`)
- CSRF exemption for webhook endpoint only
- Logs all mismatches for debugging

---

## 🚀 Production Checklist

- [ ] Update `.env` with production IPaymu credentials (IPAYMU_VA, IPAYMU_API_KEY)
- [ ] Set `IPAYMU_BASE_URL` to production: `https://ipaymu.com/api/v2`
- [ ] Test full payment flow in sandbox first
- [ ] Set up HTTPS (required for webhooks)
- [ ] Configure MAIL settings for production email delivery
- [ ] Test webhook endpoint is reachable from IPaymu servers
- [ ] Set up monitoring/alerting for payment failures
- [ ] Document production credentials securely
- [ ] Plan rollback procedure if needed

---

## 📚 Related Documentation

- Complete Implementation Guide: `IPAYMU_IMPLEMENTATION_GUIDE.md`
- IPaymu API Reference: `README IPAYMU API.md`
- Contributing Guide: `README.md`

---

**Last Updated**: April 2, 2026  
**Status**: ✅ Fully Implemented and Ready for Testing
