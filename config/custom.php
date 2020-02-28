<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Configuration Info
    |--------------------------------------------------------------------------
    |
    |
    */

    'day_refund_limit' => env('DAY_REFUND_LIMIT', 25),
    'limit_user_age' => env('LIMIT_USER_AGE', 18),

    'stripe' =>[
        'stripe_fee' => env('STRIPE_FEE', 100),
        'platform_fee' => env('PLATFORM_FEE', 50),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'secret_connect' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
        'default_currency' => env('STRIPE_DEFAULT_CURRENCY', 'eur'),
        'max_payment_method' => env('MAX_PAYMENT_METHOD', 10)
    ],

    'countries' => [
        'it' => [
            'label' => 'Italia',
            'currency' => 'eur',
            'fake_data' => [
                'address' => [
                    'street' => 'Via Marsala, 29H',
                    'city' => 'Roma',
                    'cap' => '00185'
                ],
                'number' => '+3903549003847'
            ]
        ],
        'es' => [
            'label' => 'Spain',
            'currency' => 'eur',
            'fake_data' => [
                'address' => [
                    'street' => 'Ronda de Segovia, 11',
                    'city' => 'Madrid',
                    'cap' => '28005'
                ],
                'number' => '+34636859773'
            ]
        ],
        'gb' => [
            'label' => 'United Kingdom',
            'currency' => 'gbp',
            'fake_data' => [
                'address' => [
                    'street' => '66 Trafalgar Square, St. James\'s',
                    'city' => 'London',
                    'cap' => 'WC2N 5DS'
                ],
                'number' => '+4407036400109'
            ]
        ]
    ],

    'paginate' => env('PAGINATE_ITEMS', 20)

];
