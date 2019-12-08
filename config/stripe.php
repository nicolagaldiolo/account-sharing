<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Configuration Info
    |--------------------------------------------------------------------------
    |
    |
    */

    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'secret_connect' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

];
