<?php

return [
    /**
     * Chargily API Configuration
     */
    'mode' => env('CHARGILY_MODE', 'live'), // 'test' or 'live'
    
    'test' => [
        'base_url' => 'https://pay.chargily.net/test/api/v2',
    ],
    
    'live' => [
        'base_url' => 'https://pay.chargily.net/api/v2',
    ],
    
    'api_key' => env('CHARGILY_EPAY_KEY', ''),
    'api_secret' => env('CHARGILY_EPAY_SECRET', ''),
    
    'back_url' => env('CHARGILY_EPAY_BACK_URL', 'https://diaszone.com'),
    'webhook_url' => env('CHARGILY_EPAY_WEBHOOK_URL', ''),
];
