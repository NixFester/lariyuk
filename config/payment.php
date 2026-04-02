<?php

/**
 * Payment Gateway Configuration
 * 
 * Supported gateways: classic, ipaymu, midtrans
 * 
 * Switch payment gateway by changing PAYMENT_GATEWAY in .env
 */

return [
    'gateway' => env('PAYMENT_GATEWAY', 'classic'),
    
    'classic' => [
        'description' => 'Classic payment with manual admin verification',
        'methods' => ['qris', 'bank_transfer', 'other'],
    ],
    
    'ipaymu' => [
        'description' => 'Automated payment via IPaymu',
        'va' => env('IPAYMU_VA'),
        'api_key' => env('IPAYMU_API_KEY'),
        'base_url' => env('IPAYMU_BASE_URL', 'https://sandbox.ipaymu.com/api/v2'),
        'timeout' => 30 * 60, // 30 minutes
    ],

    'midtrans' => [
        'description' => 'Automated payment via Midtrans (Snap popup)',
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],
];
