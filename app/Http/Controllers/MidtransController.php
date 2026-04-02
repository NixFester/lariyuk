<?php

namespace App\Http\Controllers;

use App\Mail\TicketMail;
use App\Models\Registration;
use App\Services\Midtrans\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MidtransController extends Controller
{
    private MidtransService $midtrans;

    public function __construct()
    {
        $this->midtrans = new MidtransService();
    }

    /**
     * Debug page - shows all registrations and allows manual status checking
     */
    public function debugPage()
    {
        $registrations = Registration::orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('checkout.midtrans-debug', [
            'registrations' => $registrations,
        ]);
    }

    /**
     * Initiate Midtrans Snap payment
     * 
     * Display payment page with Snap popup loaded
     */
    public function initiate(string $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

        if ($registration->payment_status !== 'pending') {
            return redirect()->route('checkout.midtrabs.success', $invoice)
                ->with('info', 'Payment already processed');
        }

        try {
            // Create Snap token
            $snapData = $this->midtrans->createSnapToken(
                invoiceNumber: $invoice,
                amount: (int)$registration->total,
                subtotal: (int)$registration->subtotal,
                adminFee: (int)$registration->admin_fee,
                customerName: $registration->nama_peserta,
                customerEmail: $registration->email,
                customerPhone: $registration->phone,
                eventName: $registration->event->title
            );

            // Store snap token in database for reference
            $registration->update([
                'midtrans_transaction_id' => $invoice,
                'payment_status' => 'pending', // Explicitly set to pending while waiting for payment
            ]);

            Log::info('Midtrans Snap payment initiated', [
                'invoice' => $invoice,
                'snap_token' => substr($snapData['token'], 0, 20) . '...',
            ]);

            return view('checkout.midtrans-pending', [
                'registration' => $registration,
                'snapToken' => $snapData['token'],
                'clientKey' => config('midtrans.client_key'),
                'isProduction' => config('midtrans.is_production', false),
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans initiation error', [
                'invoice' => $invoice,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('checkout.pending', $invoice)
                ->with('error', 'Failed to initialize payment. Please try again.');
        }
    }

    /**
     * Midtrans callback - Payment finished successfully
     * 
     * User returns from Snap after successful payment
     */
    public function finish(string $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

        Log::info('Midtrans payment finished callback', ['invoice' => $invoice]);

        // Payment will be verified via webhook signature
        // For now, just redirect to success page
        // The webhook will update the payment status to 'paid'
        return view('checkout.midtrans-processing', ['registration' => $registration]);
    }

    /**
     * Midtrans callback - Payment unfinished
     * 
     * User interrupted payment process
     */
    public function unfinish(string $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

        Log::info('Midtrans payment unfinished', ['invoice' => $invoice]);

        return redirect()->route('checkout.pending', $invoice)
            ->with('warning', 'Payment was not completed. Please try again.');
    }

    /**
     * Midtrans callback - Payment error
     * 
     * Error occurred during payment
     */
    public function error(string $invoice)
    {
        $registration = Registration::where('invoice_number', $invoice)->firstOrFail();

        Log::info('Midtrans payment error', ['invoice' => $invoice]);

        return redirect()->route('checkout.pending', $invoice)
            ->with('error', 'Payment failed. Please try again.');
    }

    /**
     * Midtrans Webhook - Handle payment notifications
     * 
     * Verify signature and update payment status
     * Called by Midtrans server, bypass CSRF
     */
    public function webhook(Request $request)
    {
        $notification = $request->all();
        $orderId = $notification['order_id'] ?? '';
        
        Log::info('Midtrans webhook received', [
            'order_id' => $orderId,
            'transaction_status' => $notification['transaction_status'] ?? 'unknown',
            'fraud_status' => $notification['fraud_status'] ?? 'unknown',
        ]);

        // Verify webhook signature
        $signatureHeader = $request->header('X-Callback-Token', '');
        if (!$this->midtrans->verifyWebhookSignature($notification, $signatureHeader)) {
            Log::warning('Midtrans webhook signature verification failed', [
                'order_id' => $orderId,
                'expected_format' => 'order_id|status_code|gross_amount|server_key',
            ]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        try {
            // Find registration by order_id (which is the invoice_number)
            $registration = Registration::where('invoice_number', $orderId)->first();
            if (!$registration) {
                Log::warning('Midtrans webhook - registration not found', ['order_id' => $orderId]);
                return response()->json(['status' => 'ok'], 200);
            }

            // Check if payment is completed based on Midtrans notification
            if ($this->midtrans->isPaymentCompleted($notification)) {
                DB::transaction(function () use ($registration, $orderId, $notification) {
                    // Only update if not already paid (avoid duplicate processing)
                    if ($registration->payment_status !== 'paid') {
                        $registration->update([
                            'payment_status' => 'paid',
                            'payment_verified_at' => now(),
                            'midtrans_paid_at' => now(),
                            'midtrans_transaction_id' => $notification['transaction_id'] ?? $orderId,
                        ]);

                        // Send ticket email
                        try {
                            Mail::to($registration->email)->send(new TicketMail($registration));
                            $registration->update(['ticket_email_sent' => true]);

                            Log::info('Ticket email sent via webhook', [
                                'invoice' => $orderId,
                                'email' => $registration->email,
                                'transaction_id' => $notification['transaction_id'] ?? null,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Ticket email failed via webhook', [
                                'invoice' => $orderId,
                                'email' => $registration->email,
                                'error' => $e->getMessage(),
                            ]);
                        }

                        Log::info('Payment confirmed and verified via webhook', [
                            'order_id' => $orderId,
                            'transaction_status' => $notification['transaction_status'] ?? 'unknown',
                        ]);
                    } else {
                        Log::info('Webhook: Payment already marked as paid, skipping duplicate processing', [
                            'order_id' => $orderId,
                        ]);
                    }
                });
            } else {
                // Payment not completed (e.g., pending, cancelled, failed)
                $transactionStatus = $notification['transaction_status'] ?? 'unknown';
                Log::info('Webhook: Payment not yet completed', [
                    'order_id' => $orderId,
                    'transaction_status' => $transactionStatus,
                ]);
            }

            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook processing error', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'ok'], 200); // Return OK to prevent retries
        }
    }

    /**
     * AJAX endpoint to check payment status
     * 
     * Frontend polls this to check if payment is confirmed
     * Also queries Midtrans API for verification if still pending
     */
    public function checkStatus(Request $request)
    {
        $invoice = $request->query('invoice');
        if (!$invoice) {
            return response()->json(['error' => 'Invoice required'], 422);
        }

        try {
            $registration = Registration::where('invoice_number', $invoice)->first();
            if (!$registration) {
                return response()->json(['error' => 'Registration not found'], 404);
            }

            Log::info('checkStatus called', [
                'invoice' => $invoice,
                'current_status' => $registration->payment_status,
            ]);

            // If already paid, return immediately
            if ($registration->payment_status === 'paid') {
                return response()->json([
                    'invoice' => $invoice,
                    'payment_status' => 'paid',
                    'paid_at' => $registration->midtrans_paid_at?->toIso8601String(),
                ]);
            }

            // If still pending, actively check with Midtrans API for verification
            Log::info('Querying Midtrans API for invoice', ['invoice' => $invoice]);
            
            $transactionStatus = $this->midtrans->getTransactionStatus($invoice);
            
            Log::info('Midtrans API response received', [
                'invoice' => $invoice,
                'response' => $transactionStatus,
            ]);

            // Check if payment is completed
            $isCompleted = $this->midtrans->isPaymentCompleted($transactionStatus);
            Log::info('isPaymentCompleted result', [
                'invoice' => $invoice,
                'is_completed' => $isCompleted,
                'status' => $transactionStatus['transaction_status'] ?? 'unknown',
            ]);

            if ($isCompleted) {
                Log::info('Payment confirmed by API - updating database', ['invoice' => $invoice]);
                
                DB::transaction(function () use ($registration, $invoice, $transactionStatus) {
                    // Update payment status to paid
                    $registration->update([
                        'payment_status' => 'paid',
                        'payment_verified_at' => now(),
                        'midtrans_paid_at' => now(),
                        'midtrans_transaction_id' => $transactionStatus['transaction_id'] ?? $invoice,
                    ]);

                    Log::info('Database updated to paid', [
                        'invoice' => $invoice,
                        'updated_status' => $registration->fresh()->payment_status,
                    ]);

                    // Send ticket email
                    try {
                        Mail::to($registration->email)->send(new TicketMail($registration));
                        $registration->update(['ticket_email_sent' => true]);

                        Log::info('Ticket email sent', [
                            'invoice' => $invoice,
                            'email' => $registration->email,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send ticket email', [
                            'invoice' => $invoice,
                            'email' => $registration->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                });

                return response()->json([
                    'invoice' => $invoice,
                    'payment_status' => 'paid',
                    'paid_at' => now()->toIso8601String(),
                ]);
            }

            // Payment not yet confirmed by Midtrans
            Log::info('Payment not yet completed', [
                'invoice' => $invoice,
                'status' => $transactionStatus['transaction_status'] ?? 'unknown',
            ]);

            return response()->json([
                'invoice' => $invoice,
                'payment_status' => $registration->payment_status,
                'transaction_status' => $transactionStatus['transaction_status'] ?? 'unknown',
                'paid_at' => $registration->midtrans_paid_at?->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('checkStatus exception', [
                'invoice' => $invoice ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'invoice' => $invoice ?? null,
                'payment_status' => $registration->payment_status ?? 'unknown',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Manual status check endpoint - for testing/debugging
     * 
     * Manually force a status check with Midtrans without waiting for webhook
     */
    public function manualCheckStatus(Request $request)
    {
        $invoice = $request->query('invoice');
        if (!$invoice) {
            return response()->json(['error' => 'Invoice required'], 422);
        }

        try {
            $registration = Registration::where('invoice_number', $invoice)->first();
            if (!$registration) {
                return response()->json(['error' => 'Registration not found'], 404);
            }

            Log::info('Manual status check initiated', ['invoice' => $invoice]);

            $transactionStatus = $this->midtrans->getTransactionStatus($invoice);
            
            Log::info('Manual check - Midtrans API response', [
                'invoice' => $invoice,
                'response' => $transactionStatus,
            ]);

            // Check if payment is completed
            $isCompleted = $this->midtrans->isPaymentCompleted($transactionStatus);
            Log::info('Manual check - isPaymentCompleted result', [
                'invoice' => $invoice,
                'is_completed' => $isCompleted,
            ]);

            if ($isCompleted) {
                Log::info('Manual check - Payment confirmed, updating database', ['invoice' => $invoice]);
                
                DB::transaction(function () use ($registration, $invoice, $transactionStatus) {
                    $registration->update([
                        'payment_status' => 'paid',
                        'payment_verified_at' => now(),
                        'midtrans_paid_at' => now(),
                        'midtrans_transaction_id' => $transactionStatus['transaction_id'] ?? $invoice,
                    ]);

                    Log::info('Manual check - Database updated', [
                        'invoice' => $invoice,
                        'payment_status' => 'paid',
                    ]);

                    // Send ticket email
                    try {
                        Mail::to($registration->email)->send(new TicketMail($registration));
                        $registration->update(['ticket_email_sent' => true]);

                        Log::info('Manual check - Ticket email sent', [
                            'invoice' => $invoice,
                            'email' => $registration->email,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Manual check - Ticket email failed', [
                            'invoice' => $invoice,
                            'email' => $registration->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Payment confirmed and database updated',
                    'invoice' => $invoice,
                    'payment_status' => 'paid',
                    'paid_at' => now()->toIso8601String(),
                ]);
            }

            Log::info('Manual check - Payment not yet completed', [
                'invoice' => $invoice,
                'status' => $transactionStatus['transaction_status'] ?? 'unknown',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment not yet confirmed by Midtrans',
                'invoice' => $invoice,
                'payment_status' => $transactionStatus['transaction_status'] ?? 'unknown',
                'fraud_status' => $transactionStatus['fraud_status'] ?? 'unknown',
                'database_status' => $registration->payment_status,
            ]);
        } catch (\Exception $e) {
            Log::error('Manual check exception', [
                'invoice' => $invoice ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to check status with Midtrans',
                'message' => $e->getMessage(),
                'invoice' => $invoice,
            ], 500);
        }
    }
}