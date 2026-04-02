<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MidtransPlaygroundController extends Controller
{
    private string $serverKey;
    private string $snapUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));
        $isProduction    = config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));

        $this->snapUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Show the Midtrans Snap playground page.
     */
    public function index()
    {
        $isProduction = config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $clientKey    = config('midtrans.client_key', env('MIDTRANS_CLIENT_KEY', ''));

        return view('midtrans-playground', compact('isProduction', 'clientKey'));
    }

    /**
     * Diagnose key configuration — never expose this in production!
     */
    public function diagnose()
    {
        $isProduction   = config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $serverKey      = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));
        $clientKey      = config('midtrans.client_key', env('MIDTRANS_CLIENT_KEY', ''));

        $serverPrefix   = substr($serverKey, 0, 15);
        $clientPrefix   = substr($clientKey, 0, 15);
        $expectedServer = $isProduction ? 'Mid-server-' : 'SB-Mid-server-';
        $expectedClient = $isProduction ? 'Mid-client-' : 'SB-Mid-client-';

        $serverOk = str_contains($serverKey, $expectedServer);
        $clientOk = str_contains($clientKey, $expectedClient);

        return response()->json([
            'environment' => $isProduction ? 'production' : 'sandbox',
            'snap_url'    => $this->snapUrl,
            'server_key'  => [
                'set'      => !empty($serverKey),
                'preview'  => $serverPrefix ? $serverPrefix . '...' : '(empty)',
                'expected' => 'Starts with: ' . $expectedServer,
                'ok'       => $serverOk,
            ],
            'client_key'  => [
                'set'      => !empty($clientKey),
                'preview'  => $clientPrefix ? $clientPrefix . '...' : '(empty)',
                'expected' => 'Starts with: ' . $expectedClient,
                'ok'       => $clientOk,
            ],
            'tip' => (!$serverOk || !$clientOk)
                ? 'Key prefix mismatch — you may be using production keys on sandbox, or vice versa.'
                : 'Keys look correct. If you still get 401, run: php artisan config:clear',
        ]);
    }

    /**
     * Generate a Snap token by calling the Midtrans API.
     */
    public function token(Request $request)
    {
        $request->validate([
            'amount'         => 'required|integer|min:1000',
            'customer_name'  => 'required|string|max:100',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string|max:20',
            'item_name'      => 'required|string|max:200',
        ]);

        if (empty($this->serverKey)) {
            return response()->json([
                'error' => 'MIDTRANS_SERVER_KEY is not set in your .env file.',
            ], 422);
        }

        $orderId = 'PLAYGROUND-' . strtoupper(Str::random(8)) . '-' . time();

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $request->amount,
            ],
            'item_details' => [
                [
                    'id'       => 'ITEM-01',
                    'price'    => (int) $request->amount,
                    'quantity' => 1,
                    'name'     => $request->item_name,
                ],
            ],
            'customer_details' => [
                'first_name' => $request->customer_name,
                'email'      => $request->customer_email,
                'phone'      => $request->customer_phone ?? '',
            ],
        ];

        try {
            $response = $this->callMidtransSnap($payload);

            return response()->json([
                'token'    => $response['token'],
                'order_id' => $orderId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function callMidtransSnap(array $payload): array
    {
        $isProduction = config('midtrans.is_production', env('MIDTRANS_IS_PRODUCTION', false));
        $caInfo       = env('MIDTRANS_CAINFO', '');

        $ch = curl_init($this->snapUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($this->serverKey . ':'),
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => $isProduction,
            CURLOPT_SSL_VERIFYHOST => $isProduction ? 2 : 0,
            ...($caInfo ? [CURLOPT_CAINFO => $caInfo] : []),
        ]);

        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('cURL error: ' . $error);
        }

        $data = json_decode($result, true);

        if ($httpCode !== 201) {
            $msg = $data['error_messages'][0] ?? $data['message'] ?? 'Midtrans API error (HTTP ' . $httpCode . ')';
            throw new \RuntimeException($msg);
        }

        return $data;
    }
}