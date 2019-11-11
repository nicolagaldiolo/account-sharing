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
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

];
