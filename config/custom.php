<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Configuration Info
    |--------------------------------------------------------------------------
    |
    |
    */

    'day_refund_limit' => env('DAY_REFUND_LIMIT', '25'),

    'stripe' =>[
        'stripe_fee' => env('STRIPE_FEE', 100),
        'platform_fee' => env('PLATFORM_FEE', 50),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'secret_connect' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ]

];
