<?php

namespace App\Services\Midtrans;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransService
{
    private string $serverKey;
    private string $clientKey;
    private bool $isProduction;
    private string $snapUrl;
    private string $apiUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production', false);

        if (empty($this->serverKey) || empty($this->clientKey)) {
            Log::error('Midtrans keys not configured', [
                'server_key_set' => !empty($this->serverKey),
                'client_key_set' => !empty($this->clientKey),
            ]);
            throw new \Exception('Midtrans API keys not configured in .env');
        }

        $base = $this->isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
        $this->snapUrl = $base . '/snap/v1/transactions';
        $this->apiUrl = $base . '/api/v1';
    }

    /**
     * Create Snap payment token for popup/redirect mode
     * 
     * @param string $invoiceNumber
     * @param int $amount Total gross amount
     * @param int $subtotal Event price
     * @param int $adminFee Admin fee amount
     * @param string $customerName
     * @param string $customerEmail
     * @param string $customerPhone
     * @param string $eventName
     * 
     * @return array ['token' => '...', 'redirect_url' => '...']
     */
    public function createSnapToken(
        string $invoiceNumber,
        int $amount,
        int $subtotal,
        int $adminFee,
        string $customerName,
        string $customerEmail,
        string $customerPhone,
        string $eventName
    ): array
    {
        // Truncate event name if too long (Midtrans limit is 50 chars)
        $eventNameTruncated = substr('Event: ' . $eventName, 0, 50);
        
        $payload = [
            'transaction_details' => [
                'order_id' => $invoiceNumber,
                'gross_amount' => (int)$amount,
            ],
            'customer_details' => [
                'first_name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
            ],
            'item_details' => [
                [
                    'id' => 'EVENT',
                    'price' => $subtotal,
                    'quantity' => 1,
                    'name' => $eventNameTruncated,
                ],
                [
                    'id' => 'ADMIN_FEE',
                    'price' => $adminFee,
                    'quantity' => 1,
                    'name' => 'Admin Fee',
                ]
            ],
            'callbacks' => [
                'finish' => route('checkout.midtrans.finish', $invoiceNumber),
                'unfinish' => route('checkout.midtrans.unfinish', $invoiceNumber),
                'error' => route('checkout.midtrans.error', $invoiceNumber),
            ],
        ];

        try {
            $response = $this->callSnapApi('POST', $this->snapUrl, $payload);

            if (empty($response['token'])) {
                Log::error('Midtrans token creation failed - no token in response', [
                    'order_id' => $invoiceNumber,
                    'response' => $response,
                ]);
                throw new \Exception('Failed to create Snap token');
            }

            $snapUrl = $this->isProduction
                ? 'https://app.midtrans.com/snap/snap.js'
                : 'https://app.sandbox.midtrans.com/snap/snap.js';

            Log::info('Midtrans Snap token created', [
                'order_id' => $invoiceNumber,
                'token' => substr($response['token'], 0, 20) . '...',
            ]);

            return [
                'token' => $response['token'],
                'redirect_url' => $response['redirect_url'] ?? null,
                'snap_script_url' => $snapUrl,
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Snap token creation error', [
                'order_id' => $invoiceNumber,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check transaction status
     * 
     * @param string $orderId
     * 
     * @return array Transaction status details
     */
    public function getTransactionStatus(string $orderId): array
    {
        try {
            $url = $this->apiUrl . '/transactions/' . $orderId . '/status';
            $response = $this->callApi('GET', $url, []);

            Log::info('Midtrans transaction status retrieved', [
                'order_id' => $orderId,
                'status' => $response['transaction_status'] ?? 'unknown',
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Midtrans status check error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify webhook signature (ISO-8859-1)
     * 
     * @param array $notification
     * @param string $signatureHeader
     * 
     * @return bool
     */
    public function verifyWebhookSignature(array $notification, string $signatureHeader): bool
    {
        // Use server key for verification
        $orderId = $notification['order_id'] ?? '';
        $statusCode = $notification['status_code'] ?? '';
        $grossAmount = $notification['gross_amount'] ?? '';

        // Compute hash
        $data = $orderId . $statusCode . $grossAmount . $this->serverKey;
        $computedHash = hash('sha512', $data);

        // Constant-time comparison to prevent timing attacks
        return hash_equals($computedHash, $signatureHeader);
    }

    /**
     * Check if payment is completed based on transaction status
     * 
     * @param array $transactionStatus
     * 
     * @return bool
     */
    public function isPaymentCompleted(array $transactionStatus): bool
    {
        $status = $transactionStatus['transaction_status'] ?? '';
        $fraudStatus = $transactionStatus['fraud_status'] ?? '';

        Log::debug('Checking if payment completed', [
            'transaction_status' => $status,
            'fraud_status' => $fraudStatus,
            'full_response' => $transactionStatus,
        ]);

        // Payment is completed if not fraud and status is settlement/capture
        if ($fraudStatus === 'challenge') {
            Log::info('Payment challenged - not completing', ['order_id' => $transactionStatus['order_id'] ?? 'unknown']);
            return false; // Pending challenge
        }

        // Accept all payment completion statuses
        $completedStatuses = [
            'capture',    // Credit card - captured
            'settlement', // Successfully settled
            'accept',     // Payment accepted
            'success',    // General success indicator
        ];

        $isCompleted = in_array($status, $completedStatuses);
        
        Log::info('Payment completion check result', [
            'order_id' => $transactionStatus['order_id'] ?? 'unknown',
            'status' => $status,
            'is_completed' => $isCompleted,
            'accepted_statuses' => $completedStatuses,
        ]);

        return $isCompleted;
    }

    /**
     * Make authenticated API call to Midtrans
     * 
     * @param string $method
     * @param string $url
     * @param array $payload
     * 
     * @return array
     */
    private function callApi(string $method, string $url, array $payload): array
    {
        $client = new Client();

        $options = [
            'auth' => [$this->serverKey, ''],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'timeout' => 10,
            'verify' => false, // Disable SSL verification for localhost/development
        ];

        if ($method === 'POST' && !empty($payload)) {
            $options['json'] = $payload;
        }

        try {
            Log::debug('Midtrans API call', [
                'method' => $method,
                'url' => $url,
                'has_auth' => !empty($this->serverKey),
                'is_production' => $this->isProduction,
            ]);

            $response = $client->request($method, $url, $options);
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $decoded = json_decode($body, true) ?? [];

            Log::debug('Midtrans API response', [
                'url' => $url,
                'status_code' => $statusCode,
                'response' => $decoded,
            ]);

            return $decoded;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode() ?? 'unknown';
            $response = $e->getResponse()?->getBody()->getContents() ?? 'No response body';
            
            Log::error('Midtrans API HTTP error', [
                'method' => $method,
                'url' => $url,
                'http_status' => $statusCode,
                'error' => $e->getMessage(),
                'response_body' => $response,
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Midtrans API call failed', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage(),
                'exception_type' => get_class($e),
            ]);
            throw $e;
        }
    }

    /**
     * Call Snap API
     * 
     * @param string $method
     * @param string $url
     * @param array $payload
     * 
     * @return array
     */
    private function callSnapApi(string $method, string $url, array $payload): array
    {
        return $this->callApi($method, $url, $payload);
    }
}
