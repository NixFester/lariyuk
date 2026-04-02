<?php

namespace App\Http\Controllers;

use App\Mail\TicketMail;
use App\Models\Registration;
use App\Services\IPaymu\IPaymuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class IPaymuController extends Controller
{
    protected IPaymuService $ipaymu;

    public function __construct(IPaymuService $ipaymu)
    {
        $this->ipaymu = $ipaymu;
    }

    /**
     * Initiate IPaymu payment
     * 
     * Receives invoice from the registration flow and creates a charge
     * Then redirects to ipaymu-pending page for status polling
     */
    public function initiate(string $invoice)
    {
        $registration = Registration::with(['event', 'category'])
            ->where('invoice_number', $invoice)->firstOrFail();

        // If already paid, redirect to success
        if ($registration->payment_status === 'paid') {
            return redirect()->route('checkout.success', $invoice);
        }

        // Create charge via IPaymu API
        $chargeData = [
            'name' => $registration->nama_peserta,
            'email' => $registration->email,
            'phone' => $registration->phone,
            'amount' => $registration->total,
            'invoice' => $registration->invoice_number,
            'referenceId' => $registration->invoice_number,
            'description' => 'Event Registration - ' . ($registration->event->name ?? 'Event'),
            'comments' => 'Payment for ' . $registration->nama_peserta,
            'notifyUrl' => route('checkout.ipaymu.webhook'),
        ];

        $response = $this->ipaymu->createCharge($chargeData);

        Log::info('IPaymu createCharge response', ['response' => $response, 'invoice' => $invoice]);

        if (!$response) {
            Log::error('IPaymu charge creation returned null', ['invoice' => $invoice]);
            return redirect()->route('checkout.pending', $invoice)
                ->with('error', 'Gagal membuat transaksi. Silakan coba lagi.');
        }

        // Extract transaction ID from response (official library format)
        $transactionId = $response['data']['id'] ?? $response['data']['transactionId'] ?? $response['id'] ?? $response['referenceId'] ?? null;

        if (!$transactionId) {
            Log::error('IPaymu charge creation failed - no transaction ID', ['response' => $response, 'invoice' => $invoice]);
            return redirect()->route('checkout.pending', $invoice)
                ->with('error', 'Gagal membuat transaksi. Silakan coba lagi.');
        }

        // Store IPaymu transaction ID
        $registration->update([
            'ipaymu_transaction_id' => $transactionId,
        ]);

        return view('checkout.ipaymu-pending', compact('registration'));
    }

    /**
     * AJAX endpoint for checking payment status
     * 
     * Called by JavaScript polling every 5 seconds
     */
    public function checkStatus(Request $request)
    {
        $invoice = $request->query('invoice');
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

        // If already marked as paid, return success
        if ($registration->payment_status === 'paid') {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified!',
                'isPaid' => true,
            ]);
        }

        // Check status with IPaymu
        if (!$registration->ipaymu_transaction_id) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Waiting for payment...',
                'isPaid' => false,
            ]);
        }

        $statusResponse = $this->ipaymu->checkStatus($registration->ipaymu_transaction_id);

        if (!$statusResponse) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Checking payment status...',
                'isPaid' => false,
            ]);
        }

        $isSuccess = $this->ipaymu->isPaymentSuccessful($statusResponse);

        if ($isSuccess) {
            // Mark as paid
            $registration->update([
                'payment_status' => 'paid',
                'payment_verified_at' => now(),
                'ipaymu_paid_at' => now(),
            ]);

            // Send ticket email
            try {
                Mail::to($registration->email)->send(new TicketMail($registration));
                $registration->update(['ticket_email_sent' => true]);
                Log::info("Ticket email sent to {$registration->email} — {$registration->invoice_number}");
            } catch (\Exception $e) {
                Log::error("Ticket email failed for {$registration->invoice_number}: " . $e->getMessage());
            }

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

    /**
     * Webhook endpoint for IPaymu payment notifications
     * 
     * IPaymu will POST here when payment is completed
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-IPAYMU-SIGNATURE') ?? '';

        Log::info('IPaymu webhook received', $payload);

        // Verify signature
        if (!$this->ipaymu->verifySignature($payload, $signature)) {
            Log::warning('IPaymu webhook signature verification failed', ['payload' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        // Get reference ID from payload
        $referenceId = $payload['referenceId'] ?? $payload['data']['referenceId'] ?? null;

        if (!$referenceId) {
            Log::warning('No reference ID in webhook', ['payload' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'No reference ID'], 400);
        }

        // Find registration by invoice
        $registration = Registration::where('invoice_number', $referenceId)->first();

        if (!$registration) {
            Log::warning('Registration not found for webhook', ['reference_id' => $referenceId]);
            return response()->json(['status' => 'error', 'message' => 'Registration not found'], 404);
        }

        // Check if payment is successful
        if ($this->ipaymu->isPaymentSuccessful($payload)) {
            // Mark as paid
            $registration->update([
                'payment_status' => 'paid',
                'payment_verified_at' => now(),
                'ipaymu_paid_at' => now(),
            ]);

            // Send ticket email
            try {
                Mail::to($registration->email)->send(new TicketMail($registration));
                $registration->update(['ticket_email_sent' => true]);
                Log::info("Ticket email sent (webhook) to {$registration->email} — {$registration->invoice_number}");
            } catch (\Exception $e) {
                Log::error("Ticket email failed for {$registration->invoice_number}: " . $e->getMessage());
            }

            return response()->json(['status' => 'success', 'message' => 'Payment verified']);
        }

        return response()->json(['status' => 'pending', 'message' => 'Payment not yet confirmed']);
    }

    /**
     * Display API testing page
     */
    public function testPage()
    {
        return view('checkout.ipaymu-test');
    }

    /**
     * Run API test
     * 
     * Tests if the API key and VA are correct by checking balance
     */
    public function runTest(Request $request)
    {
        Log::info('IPaymu API test started');

        try {
            // Test 1: Check if API credentials are loaded
            $apiKey = config('services.ipaymu.api_key');
            $va = config('services.ipaymu.va');

            if (!$apiKey || !$va) {
                return response()->json([
                    'success' => false,
                    'message' => 'API credentials missing in configuration',
                    'details' => 'Please check .env file for IPAYMU_API_KEY and IPAYMU_VA',
                    'apiKey' => $apiKey ? 'Set' : 'Not set',
                    'va' => $va ? 'Set' : 'Not set',
                ], 400);
            }

            // Test 2: Try to check balance (requires valid credentials)
            $balanceResult = $this->ipaymu->checkBalance();

            Log::info('IPaymu balance check result', ['result' => $balanceResult]);

            if ($balanceResult && isset($balanceResult['success'])) {
                if ($balanceResult['success'] === true || $balanceResult['status'] === 200 || $balanceResult['status'] === 'success') {
                    return response()->json([
                        'success' => true,
                        'message' => 'API Key and VA are valid ✓',
                        'details' => $balanceResult,
                        'balance' => $balanceResult['data']['balance'] ?? 'N/A',
                    ]);
                } elseif (isset($balanceResult['message'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'API returned an error: ' . $balanceResult['message'],
                        'details' => $balanceResult,
                    ]);
                }
            }

            // If we get here with a response, analyze it
            if (is_array($balanceResult)) {
                $hasError = isset($balanceResult['status']) && $balanceResult['status'] !== 200 && $balanceResult['status'] !== 'success';
                
                if ($hasError) {
                    return response()->json([
                        'success' => false,
                        'message' => 'API returned an error',
                        'details' => $balanceResult,
                        'hint' => 'Check if API key or VA is incorrect',
                    ]);
                }

                // Check for empty or unexpected response
                if (empty($balanceResult)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'API returned an empty response',
                        'details' => 'This might indicate network issues or server unavailability',
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'API response received (appears valid)',
                    'details' => $balanceResult,
                ]);
            }

            // Null or invalid response
            if ($balanceResult === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'API returned null - This suggests invalid credentials',
                    'details' => 'Possible causes: Invalid API key, Invalid VA, Network issue, or IPaymu API is down',
                    'hint' => 'Double-check IPAYMU_API_KEY and IPAYMU_VA in .env file',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unexpected API response',
                'details' => $balanceResult,
            ]);

        } catch (\Exception $e) {
            Log::error('IPaymu API test error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed with exception: ' . $e->getMessage(),
                'details' => [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }
}
