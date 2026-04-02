@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">iPaymu API Credentials Tester</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        This tool helps verify if your iPaymu API key and Virtual Account (VA) credentials are correctly configured.
                    </p>

                    <!-- Test Status Messages -->
                    <div id="test-loading" class="alert alert-info d-none" role="alert">
                        <div class="spinner-border spinner-border-sm me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span>Testing API credentials... Please wait.</span>
                    </div>

                    <div id="test-success" class="alert alert-success d-none" role="alert">
                        <h5 class="alert-heading mb-3">✓ Success!</h5>
                        <div id="test-success-details"></div>
                    </div>

                    <div id="test-error" class="alert alert-danger d-none" role="alert">
                        <h5 class="alert-heading mb-3">✗ Error</h5>
                        <div id="test-error-message"></div>
                        <div id="test-error-details" class="mt-3 small"></div>
                    </div>

                    <!-- Configuration Status -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Configuration Status</h6>
                            <div class="row">
                                <div class="col-sm-6">
                                    <p class="mb-2">
                                        <strong>API Key:</strong>
                                        <span class="badge bg-secondary" id="config-api-key">Not checked</span>
                                    </p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-2">
                                        <strong>VA (Virtual Account):</strong>
                                        <span class="badge bg-secondary" id="config-va">Not checked</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Button -->
                    <div class="mb-4">
                        <button id="test-btn" class="btn btn-primary btn-lg w-100" onclick="runTest()">
                            <i class="fas fa-flask me-2"></i> Test iPaymu API
                        </button>
                    </div>

                    <!-- Raw Response (Hidden by default) -->
                    <div class="accordion mb-4">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rawResponse">
                                    Raw API Response
                                </button>
                            </h2>
                            <div id="rawResponse" class="accordion-collapse collapse" data-bs-parent=".accordion">
                                <div class="accordion-body">
                                    <pre class="mb-0" id="raw-response-body" style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Section -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Troubleshooting</h6>
                            <ul class="mb-0 small">
                                <li><strong>API Key Invalid:</strong> Check your .env file for IPAYMU_API_KEY. Get it from your iPaymu dashboard.</li>
                                <li><strong>VA Invalid:</strong> Verify IPAYMU_VA in .env file. This should be your iPaymu Virtual Account number.</li>
                                <li><strong>Network Error:</strong> Check your internet connection and firewall settings.</li>
                                <li><strong>Cannot connect to iPaymu:</strong> The iPaymu API server might be down. Try again later.</li>
                                <li><strong>Sandbox vs Production:</strong> Make sure IPAYMU_PRODUCTION mode matches your credentials.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Example Flow Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Example from README</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">This test uses the official iPaymu SDK to verify credentials:</p>
                    <pre class="mb-0"><code class="language-php">// Check Balance
$balance = $iPaymu->checkBalance();

// If balance returns successfully, credentials are valid ✓</code></pre>
                </div>
            </div>

            <!-- POST Redirect Payment API Documentation -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">POST - Redirect Payment (iPaymu Payment Page)</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">
                        <strong>Endpoint:</strong> 
                        <code>https://sandbox.ipaymu.com/api/v2/payment</code>
                    </p>
                    <p class="text-muted small mb-3">
                        Pembayaran menggunakan halaman pembayaran iPaymu. Integrasi yang sangat mudah, tanpa membuat halaman pembayaran lagi.
                    </p>

                    <div class="mb-3">
                        <h6 class="text-decoration-underline">HEADERS</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Header</th>
                                        <th>Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>Content-Type</code></td>
                                        <td><code>application/json</code></td>
                                        <td>-</td>
                                    </tr>
                                    <tr>
                                        <td><code>signature</code></td>
                                        <td><code>Signature</code></td>
                                        <td>Generate per-request. Read <a href="https://storage.googleapis.com/ipaymu-docs/ipaymu-api/iPaymu-signature-documentation-v2.pdf" target="_blank">signature docs</a></td>
                                    </tr>
                                    <tr>
                                        <td><code>va</code></td>
                                        <td><code>1179000899</code></td>
                                        <td>Virtual Account number</td>
                                    </tr>
                                    <tr>
                                        <td><code>timestamp</code></td>
                                        <td><code>2026-04-01T07:07:46.866Z</code></td>
                                        <td>ISO 8601 format</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="text-decoration-underline">BODY PARAMETERS (Form Data)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Example Value</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>product[]</code></td>
                                        <td>T-Shirt</td>
                                        <td>Product name</td>
                                    </tr>
                                    <tr>
                                        <td><code>qty[]</code></td>
                                        <td>2</td>
                                        <td>Quantity</td>
                                    </tr>
                                    <tr>
                                        <td><code>price[]</code></td>
                                        <td>51000</td>
                                        <td>Price in IDR</td>
                                    </tr>
                                    <tr>
                                        <td><code>description[]</code></td>
                                        <td>Size XL</td>
                                        <td>Product description (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>imageUrl[]</code></td>
                                        <td>https://demo.ipaymu.com/...</td>
                                        <td>Product image URL (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>weight[]</code></td>
                                        <td>0.5</td>
                                        <td>Product weight in kg (optional, for COD)</td>
                                    </tr>
                                    <tr>
                                        <td><code>length[], width[], height[]</code></td>
                                        <td>1, 1, 1</td>
                                        <td>Dimensions in cm (optional, for COD)</td>
                                    </tr>
                                    <tr>
                                        <td><code>referenceId</code></td>
                                        <td>ID1234</td>
                                        <td>Reference/transaction ID (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>returnUrl</code></td>
                                        <td>https://your-website.com/thank-you</td>
                                        <td>Thank you page URL</td>
                                    </tr>
                                    <tr>
                                        <td><code>notifyUrl</code></td>
                                        <td>https://webhook.site/...</td>
                                        <td>Webhook URL for payment notifications</td>
                                    </tr>
                                    <tr>
                                        <td><code>cancelUrl</code></td>
                                        <td>https://your-website.com/failed</td>
                                        <td>Payment failed/cancel page URL</td>
                                    </tr>
                                    <tr>
                                        <td><code>buyerName</code></td>
                                        <td>putu</td>
                                        <td>Buyer name (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>buyerEmail</code></td>
                                        <td>putu@mail.com</td>
                                        <td>Buyer email (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>buyerPhone</code></td>
                                        <td>08123456789</td>
                                        <td>Buyer phone (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>expired</code></td>
                                        <td>24</td>
                                        <td>Payment expiration in hours (optional)
                                            <br><small class="text-muted">BSI max 3h, BCA 12h (fixed), BRI max 2h, Alfamart 24h (fixed), QRIS 5m (fixed)</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><code>feeDirection</code></td>
                                        <td>MERCHANT</td>
                                        <td>Fee direction: MERCHANT or BUYER (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>account</code></td>
                                        <td>1179000899</td>
                                        <td>VA child account (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>paymentMethod</code></td>
                                        <td>cc</td>
                                        <td>Payment method: va, banktransfer, cstore, cod, qris, cc (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>pickupArea</code></td>
                                        <td>17473</td>
                                        <td>Delivery postal code for COD (optional)</td>
                                    </tr>
                                    <tr>
                                        <td><code>lang</code></td>
                                        <td>id</td>
                                        <td>Payment page language (optional)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-light border">
                        <strong>Note:</strong> Arrays (product[], qty[], price[], etc.) support multiple items for bulk purchases. Send them as arrays in your request.
                    </div>

                    <!-- Test Payment API Section -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="mb-3">Test Payment API</h6>
                        
                        <!-- Test Status Messages -->
                        <div id="payment-test-loading" class="alert alert-info d-none" role="alert">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>Testing Payment API... Please wait.</span>
                        </div>

                        <div id="payment-test-success" class="alert alert-success d-none" role="alert">
                            <h5 class="alert-heading mb-3">✓ Payment Request Created!</h5>
                            <div id="payment-test-success-details"></div>
                        </div>

                        <div id="payment-test-error" class="alert alert-danger d-none" role="alert">
                            <h5 class="alert-heading mb-3">✗ Error</h5>
                            <div id="payment-test-error-message"></div>
                            <div id="payment-test-error-details" class="mt-3 small"></div>
                        </div>

                        <!-- Test Button -->
                        <button id="payment-test-btn" class="btn btn-success btn-lg w-100" onclick="testPaymentAPI()">
                            <i class="fas fa-credit-card me-2"></i> Test Payment API
                        </button>

                        <!-- Raw Response (Hidden by default) -->
                        <div class="accordion mt-3">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#paymentRawResponse">
                                        Raw Payment API Response
                                    </button>
                                </h2>
                                <div id="paymentRawResponse" class="accordion-collapse collapse" data-bs-parent=".accordion">
                                    <div class="accordion-body">
                                        <pre class="mb-0" id="payment-raw-response-body" style="background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto;"></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.2em;
    }
</style>

<script>
async function runTest() {
    const testBtn = document.getElementById('test-btn');
    const loadingAlert = document.getElementById('test-loading');
    const successAlert = document.getElementById('test-success');
    const errorAlert = document.getElementById('test-error');
    const rawResponseBody = document.getElementById('raw-response-body');

    // Hide all alerts
    successAlert.classList.add('d-none');
    errorAlert.classList.add('d-none');
    loadingAlert.classList.remove('d-none');

    // Disable button
    testBtn.disabled = true;

    try {
        const response = await fetch('{{ route("ipaymu.test.run") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        const data = await response.json();

        // Hide loading
        loadingAlert.classList.add('d-none');

        // Display raw response
        rawResponseBody.textContent = JSON.stringify(data.details || data, null, 2);

        if (data.success) {
            // Show success
            const details = `
                <p><strong>API Key:</strong> ✓ Valid</p>
                <p><strong>VA (Virtual Account):</strong> ✓ Valid</p>
                ${data.balance ? `<p><strong>Account Balance:</strong> ${data.balance}</p>` : ''}
                <p class="mb-0 small text-muted">Your iPaymu credentials are correctly configured and ready to use.</p>
            `;
            document.getElementById('test-success-details').innerHTML = details;
            successAlert.classList.remove('d-none');
        } else {
            // Show error
            document.getElementById('test-error-message').innerHTML = `<p>${data.message}</p>`;
            
            const detailsHtml = data.details 
                ? `<pre>${JSON.stringify(data.details, null, 2)}</pre>`
                : '';
            
            const hintHtml = data.hint 
                ? `<p class="mb-0"><strong>Hint:</strong> ${data.hint}</p>`
                : '';
            
            document.getElementById('test-error-details').innerHTML = detailsHtml + hintHtml;
            errorAlert.classList.remove('d-none');
        }

    } catch (error) {
        loadingAlert.classList.add('d-none');
        document.getElementById('test-error-message').innerHTML = `<p>Failed to run test: ${error.message}</p>`;
        errorAlert.classList.remove('d-none');
    } finally {
        testBtn.disabled = false;
    }
}

async function testPaymentAPI() {
    const testBtn = document.getElementById('payment-test-btn');
    const loadingAlert = document.getElementById('payment-test-loading');
    const successAlert = document.getElementById('payment-test-success');
    const errorAlert = document.getElementById('payment-test-error');
    const rawResponseBody = document.getElementById('payment-raw-response-body');

    // Hide all alerts
    successAlert.classList.add('d-none');
    errorAlert.classList.add('d-none');
    loadingAlert.classList.remove('d-none');

    // Disable button
    testBtn.disabled = true;

    try {
        const response = await fetch('{{ route("ipaymu.test.payment") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                product: ['T-Shirt'],
                qty: [2],
                price: [51000],
                description: ['Size XL'],
                buyerName: 'Test Buyer',
                buyerEmail: 'test@example.com',
                buyerPhone: '08123456789',
            }),
        });

        const data = await response.json();

        // Hide loading
        loadingAlert.classList.add('d-none');

        // Display raw response
        rawResponseBody.textContent = JSON.stringify(data.response || data, null, 2);

        if (data.success) {
            // Show success
            const details = `
                <p><strong>Transaction ID:</strong> ${data.transactionId || 'N/A'}</p>
                <p><strong>Reference ID:</strong> ${data.referenceId || 'N/A'}</p>
                ${data.paymentUrl ? `<p><strong>Payment URL:</strong> <a href="${data.paymentUrl}" target="_blank">${data.paymentUrl}</a></p>` : ''}
                <p class="mb-0 small text-muted">Payment request created successfully! Customer can now proceed to payment.</p>
            `;
            document.getElementById('payment-test-success-details').innerHTML = details;
            successAlert.classList.remove('d-none');
        } else {
            // Show error
            document.getElementById('payment-test-error-message').innerHTML = `<p>${data.message}</p>`;
            
            const detailsHtml = data.response 
                ? `<pre>${JSON.stringify(data.response, null, 2)}</pre>`
                : '';
            
            const hintHtml = data.hint 
                ? `<p class="mb-0"><strong>Hint:</strong> ${data.hint}</p>`
                : '';
            
            document.getElementById('payment-test-error-details').innerHTML = detailsHtml + hintHtml;
            errorAlert.classList.remove('d-none');
        }

    } catch (error) {
        loadingAlert.classList.add('d-none');
        document.getElementById('payment-test-error-message').innerHTML = `<p>Failed to run test: ${error.message}</p>`;
        errorAlert.classList.remove('d-none');
    } finally {
        testBtn.disabled = false;
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
