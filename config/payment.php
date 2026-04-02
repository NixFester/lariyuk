<?php

/**
 * Payment Gateway Configuration
 * 
 * Supported gateways: classic, ipaymu
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
];
