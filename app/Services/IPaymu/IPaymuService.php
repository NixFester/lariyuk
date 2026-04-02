<?php

namespace App\Services\IPaymu;

use iPaymu\iPaymu;
use Illuminate\Support\Facades\Log;

class IPaymuService
{
    private iPaymu $ipaymu;
    private string $va;
    private string $apiKey;

    public function __construct()
    {
        $this->va = config('services.ipaymu.va');
        $this->apiKey = config('services.ipaymu.api_key');
        
        // Validate configuration
        if (empty($this->va) || empty($this->apiKey)) {
            Log::error('IPaymu configuration missing', [
                'va_set' => !empty($this->va),
                'api_key_set' => !empty($this->apiKey),
            ]);
            throw new \Exception('IPaymu VA or API Key is not configured in .env');
        }
        
        // Initialize IPaymu SDK
        // $production = env('APP_ENV') === 'production';
        $this->ipaymu = new iPaymu($this->apiKey, $this->va, false); // false for sandbox
    }

    /**
     * Create a payment charge via IPaymu API
     * 
     * @param array $data Payment data including:
     *   - invoice: Invoice ID (used as product ID)
     *   - name: User name
     *   - email: User email
     *   - phone: User phone number
     *   - amount: Total amount (price * quantity)
     *   - description: Product description (optional)
     *   - comments: Payment comments (optional)
     *   - paymentMethod: Payment method (default: 'va')
     *   - paymentChannel: Payment channel (default: 'bca')
     * @return array|null API response or null on failure
     */
    public function createCharge(array $data): ?array
    {
        try {
            // Validate required fields
            if (empty($data['invoice']) || empty($data['name']) || empty($data['email']) 
                || empty($data['phone']) || empty($data['amount'])) {
                throw new \Exception('Missing required payment data: invoice, name, email, phone, amount');
            }

            // Extract and validate invoice
            $invoice = $data['invoice'];
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'];
            $amount = $data['amount'];

            // Set callback URLs (as per API guide)
            $this->ipaymu->setURL([
                'ureturn' => route('checkout.success', $invoice),
                'unotify' => route('checkout.ipaymu.webhook'),
                'ucancel' => route('checkout.pending', $invoice),
            ]);

            // Set buyer information (as per API guide)
            $this->ipaymu->setBuyer([
                'name' => $name,
                'phone' => $this->formatPhone($phone),
                'email' => $email,
            ]);

            // Set payment expiration (default: 24 hours)
            $expiration = $data['expiration'] ?? ['value' => 24, 'unit' => 'hours'];
            $this->ipaymu->setExpired($expiration['value']);

            // Set payment notes/comments (optional)
            if (!empty($data['comments'])) {
                $this->ipaymu->setComments($data['comments']);
            }

            // Build cart with required array format for addCart()
            // addCart expects: product[], quantity[], price[], description[], weight[], length[], width[], height[]
            $cart = [
                'product' => [$data['description'] ?? 'Event Registration'],
                'quantity' => [1],
                'price' => [$amount],
                'description' => [$data['comments'] ?? 'Event ticket registration'],
                'weight' => [0],
                'length' => [0],
                'width' => [0],
                'height' => [0],
            ];

            // Add cart items to payment
            $this->ipaymu->addCart($cart);

            // Build payment data for directPayment API call
            // The directPayment() method requires: amount, paymentMethod, paymentChannel, referenceId, expired, expiredType
            $paymentData = [
                'amount' => (int) $amount,
                'paymentMethod' => $data['paymentMethod'] ?? 'va', // Virtual Account
                'paymentChannel' => $data['paymentChannel'] ?? 'bca', // Bank Central ASIA
                'referenceId' => (string) $invoice,
                'expired' => (int) ($expiration['value'] ?? 24),
                'expiredType' => $expiration['unit'] ?? 'hours',
            ];

            Log::info('IPaymu directPayment being called with', [
                'invoice' => $invoice,
                'va' => $this->va,
                'amount' => $paymentData['amount'],
                'paymentData' => $paymentData,
                'cart_product_count' => count($cart['product'] ?? []),
            ]);

            // Process payment using direct payment method
            $response = $this->ipaymu->directPayment($paymentData);

            Log::info('IPaymu charge created successfully', [
                'invoice' => $invoice,
                'response' => $response,
                'response_type' => gettype($response),
            ]);

            return $response;
        } catch (\iPaymu\Exceptions\Unauthorized $e) {
            Log::error('IPaymu Unauthorized (401) - Authentication Failed', [
                'va' => $this->va,
                'api_key_length' => strlen($this->apiKey),
                'invoice' => $invoice ?? 'unknown',
                'note' => 'Check IPAYMU_VA and IPAYMU_API_KEY in .env',
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('IPaymu charge creation failed', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'invoice' => $data['invoice'] ?? 'unknown',
                'payment_data' => $paymentData ?? [],
            ]);
            return null;
        }
    }

    /**
     * Check payment status via IPaymu API
     * Can use either transaction reference ID or transaction ID
     * 
     * @param string $referenceId Transaction reference ID or transaction ID
     * @return array|null Status data or null on failure
     */
    public function checkStatus(string $referenceId): ?array
    {
        try {
            if (empty($referenceId)) {
                throw new \Exception('Reference ID cannot be empty');
            }

            // Use IPaymu SDK to check transaction status
            $response = $this->ipaymu->checkTransaction($referenceId);

            // Validate response
            if (empty($response)) {
                Log::warning('Empty response from IPaymu status check', [
                    'reference_id' => $referenceId,
                ]);
                return null;
            }

            // Extract status - may be nested in response
            $status = $response['status'] ?? $response['data']['status'] ?? null;
            $transactionStatus = $response['transaction_status'] ?? $response['data']['transaction_status'] ?? null;

            Log::info('IPaymu status checked', [
                'reference_id' => $referenceId,
                'status' => $status,
                'transaction_status' => $transactionStatus,
                'full_response' => $response,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('IPaymu status check exception', [
                'message' => $e->getMessage(),
                'reference_id' => $referenceId,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Format phone number to IPaymu API format
     * Converts to international format with 62 prefix for Indonesia
     * Expected format for IPaymu: 62xxxxx (11-13 digits total)
     * 
     * Input examples: 08123456789, 0812-3456-789, +628123456789
     * Output example: 628123456789
     * 
     * @param string $phone Phone number in various formats
     * @return string Formatted phone number with 62 prefix
     * @throws \Exception If phone format is invalid
     */
    public function formatPhone(string $phone): string
    {
        // Trim whitespace
        $phone = trim($phone);

        if (empty($phone)) {
            throw new \Exception('Phone number cannot be empty');
        }

        // Remove non-numeric characters except leading +
        if (str_starts_with($phone, '+')) {
            $phone = '+' . preg_replace('/[^0-9]/', '', $phone);
            $phone = preg_replace('/^\+/', '', $phone); // Remove + for processing
        } else {
            $phone = preg_replace('/[^0-9]/', '', $phone);
        }

        // If starts with 0 (Indonesia domestic format), replace with 62
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        // If doesn't start with 62, add it
        elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        // Validate length (should be 11-13 digits: 62 + 9-11 more digits)
        if (strlen($phone) < 11 || strlen($phone) > 15) {
            Log::warning('Phone number format potentially invalid', [
                'original' => $phone,
                'length' => strlen($phone),
            ]);
        }

        Log::debug('Phone number formatted', ['formatted' => $phone]);

        return $phone;
    }

    /**
     * Verify webhook signature from IPaymu
     * IPaymu uses HMAC-SHA256 signature verification
     * 
     * @param array $data Webhook payload
     * @param string $signature Signature from request header (base64 encoded)
     * @return bool True if valid, false otherwise
     */
    public function verifySignature(array $data, string $signature): bool
    {
        try {
            if (empty($signature) || empty($this->apiKey)) {
                Log::warning('Webhook signature verification missing required data');
                return false;
            }

            // IPaymu webhook signature format: HMAC-SHA256 of JSON payload, base64 encoded
            $jsonPayload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
            // Compute signature using API key as secret
            $computedSignature = hash_hmac('sha256', $jsonPayload, $this->apiKey, true);
            $computedSignatureb64 = base64_encode($computedSignature);

            // Use hash_equals to prevent timing attacks
            $isValid = hash_equals($computedSignatureb64, $signature);

            if (!$isValid) {
                Log::warning('Webhook signature mismatch', [
                    'expected' => $computedSignatureb64,
                    'received' => $signature,
                    'payload_hash' => hash('sha256', $jsonPayload),
                ]);
            }

            return $isValid;
        } catch (\Exception $e) {
            Log::error('Webhook signature verification exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Parse webhook payload and determine if payment is successful
     * Checks for successful status indicators from IPaymu response
     * 
     * @param array $payload Webhook payload from IPaymu
     * @return bool True if payment is verified/successful
     */
    public function isPaymentSuccessful(array $payload): bool
    {
        try {
            // Check status field - may be at root level or nested in 'data'
            $status = $payload['status'] ?? $payload['data']['status'] ?? null;
            
            // IPaymu status indicators for successful payment
            // - 'completed': Payment verified and completed
            // - 'settled': Payment settled in merchant account
            // - 'success': Payment successful
            // - '1': Numeric indicator for success
            // - 'verified': Just verified
            
            $successStatuses = [
                'completed',
                'settled', 
                'success',
                'verified',
                '1',
                1,
            ];
            
            $isSuccessful = in_array($status, $successStatuses, true);
            
            Log::info('Payment success check', [
                'status' => $status,
                'is_successful' => $isSuccessful,
                'payload' => $payload,
            ]);
            
            return $isSuccessful;
        } catch (\Exception $e) {
            Log::error('Payment success check failed', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);
            return false;
        }
    }

    /**
     * Create redirect payment (alternative to direct payment)
     * Useful for credit card or other payment methods requiring external redirect
     * 
     * @param array $data Payment data (same format as createCharge)
     * @return array|null Redirect URL or null on failure
     */
    public function createRedirectPayment(array $data): ?array
    {
        try {
            // Validate required fields
            if (empty($data['invoice']) || empty($data['name']) || empty($data['email']) 
                || empty($data['phone']) || empty($data['amount'])) {
                throw new \Exception('Missing required payment data');
            }

            // Extract variables
            $invoice = $data['invoice'];
            $name = $data['name'];
            $email = $data['email'];
            $phone = $data['phone'];
            $amount = $data['amount'];

            // Set callback URLs
            $this->ipaymu->setURL([
                'ureturn' => route('checkout.success', $invoice),
                'unotify' => route('checkout.ipaymu.webhook'),
                'ucancel' => route('checkout.pending', $invoice),
            ]);

            // Set buyer information
            $this->ipaymu->setBuyer([
                'name' => $name,
                'phone' => $this->formatPhone($phone),
                'email' => $email,
            ]);

            // Set payment expiration
            $expiration = $data['expiration'] ?? ['value' => 24, 'unit' => 'hours'];
            $this->ipaymu->setExpired($expiration['value']);

            // Set comments
            if (!empty($data['comments'])) {
                $this->ipaymu->setComments($data['comments']);
            }

            // Build cart with required array format
            $cart = [
                'product' => [$data['description'] ?? 'Event Registration'],
                'quantity' => [1],
                'price' => [$amount],
                'description' => [$data['comments'] ?? 'Event registration'],
                'weight' => [0],
                'length' => [0],
                'width' => [0],
                'height' => [0],
            ];

            $this->ipaymu->addCart($cart);

            // Build payment data for redirectPayment API call
            $paymentData = [
                'amount' => $amount,
                'paymentMethod' => $data['paymentMethod'] ?? 'va',
                'paymentChannel' => $data['paymentChannel'] ?? 'bca',
                'referenceId' => $invoice,
                'expired' => $expiration['value'] ?? 24,
                'expiredType' => $expiration['unit'] ?? 'hours',
            ];

            // Use redirect payment method (typically for credit card)
            $response = $this->ipaymu->redirectPayment($paymentData);

            Log::info('IPaymu redirect payment created', [
                'invoice' => $invoice,
                'response' => $response,
            ]);

            return $response;
        } catch (\iPaymu\Exceptions\Unauthorized $e) {
            Log::error('IPaymu Unauthorized (401) - Authentication Failed (Redirect)', [
                'va' => $this->va,
                'invoice' => $invoice ?? 'unknown',
                'note' => 'Check IPAYMU_VA and IPAYMU_API_KEY in .env',
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('IPaymu redirect payment creation failed', [
                'message' => $e->getMessage(),
                'invoice' => $data['invoice'] ?? 'unknown',
            ]);
            return null;
        }
    }

    /**
     * Check account balance
     * Utility method to verify merchant account balance
     * 
     * @return array|null Balance data or null on failure
     */
    public function checkBalance(): ?array
    {
        try {
            $response = $this->ipaymu->checkBalance();

            Log::info('IPaymu balance checked', ['balance' => $response]);

            return $response;
        } catch (\Exception $e) {
            Log::error('IPaymu balance check failed', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Parse IPaymu API response for errors
     * Utility method to extract error information from responses
     * 
     * @param array $response API response
     * @return string|null Error message or null if no error
     */
    public function getErrorFromResponse(array $response): ?string
    {
        $error = $response['error'] ?? $response['error_message'] ?? 
                 $response['message'] ?? $response['data']['message'] ?? null;

        return $error ? (string)$error : null;
    }

    /**
     * Extract transaction ID from charge response
     * 
     * @param array $response Charge response from IPaymu
     * @return string|null Transaction ID or null if not found
     */
    public function getTransactionIdFromResponse(array $response): ?string
    {
        $transactionId = $response['data']['trx_id'] ?? $response['trx_id'] ?? 
                        $response['data']['id'] ?? $response['id'] ?? null;

        return $transactionId ? (string)$transactionId : null;
    }

    /**
     * Extract VA number from charge response
     * 
     * @param array $response Charge response from IPaymu
     * @return string|null VA number or null if not found
     */
    public function getVANumberFromResponse(array $response): ?string
    {
        $va = $response['data']['va'] ?? $response['va'] ?? 
              $response['data']['virtual_account'] ?? $response['virtual_account'] ?? null;

        return $va ? (string)$va : null;
    }
}
