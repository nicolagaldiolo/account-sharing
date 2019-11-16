<?php

return [
    'sharing' => [
        // class of your domain object
        'class' => \App\SharingUser::class,

        // name of the graph (default is "default")
        'graph' => 'sharing',

        // property of your object holding the actual state (default is "state")
        'property_path' => 'status',

        'metadata' => [
            'title' => 'Article State Machine',
        ],

        'states' => [
            [
                'name' => \App\Enums\SharingStatus::Pending,
                'metadata' => ['title' => 'Richiesta in stato di pending'],
            ],
            [
                'name' => \App\Enums\SharingStatus::Approved,
                'metadata' => ['title' => 'Richiesta approvata'],
            ],
            [
                'name' => \App\Enums\SharingStatus::Refused,
                'metadata' => ['title' => 'Richiesta rifiutata'],
            ],
            [
                'name' => \App\Enums\SharingStatus::Joined,
                'metadata' => ['title' => 'Fai parte di questo gruppo'],
            ],
            [
                'name' => \App\Enums\SharingStatus::Left,
                'metadata' => ['title' => 'Hai abbandonato questo gruppo'],
            ],
        ],

        // list of all possible states
        /*'states' => [
            // a state as associative array
            ['name' => 'new'],
            // a state as associative array with metadata
            [
                'name' => 'pending_review',
                'metadata' => ['title' => 'Pending Review'],
            ],
            // states as string
            'awaiting_changes',
            'accepted',
            'published',
            'rejected',
        ],
        */

        // list of all possible transitions
        'transitions' => [
            'approve' => [
                'from' => [\App\Enums\SharingStatus::Pending, \App\Enums\SharingStatus::Refused],
                'to' => \App\Enums\SharingStatus::Approved,
                'metadata' => ['title' => 'Approva la richiesta'],
            ],
            'refuses' => [
                'from' =>  [\App\Enums\SharingStatus::Pending, \App\Enums\SharingStatus::Approved],
                'to' => \App\Enums\SharingStatus::Refused,
                'metadata' => ['title' => 'Rifiuta la richiesta'],
            ],
            'pay' => [
                'from' => [\App\Enums\SharingStatus::Approved],
                'to' => \App\Enums\SharingStatus::Joined,
                'metadata' => ['title' => 'Paga il servizio'],
            ],

            // Callable only by program
            'left' => [
                'from' => [\App\Enums\SharingStatus::Joined],
                'to' => \App\Enums\SharingStatus::Left,
                'metadata' => ['title' => 'Abbandona il gruppo'],
            ],
        ],

        // list of all callbacks
        'callbacks' => [
            // will be called when testing a transition
            'guard' => [
                'guard_on_approving' => [
                    // call the callback on a specific transition
                    'on' => 'approve',
                    // will check the ability on the gate or the class policy
                    'can' => 'manage-own-sharing',
                ],
                'guard_on_refusing' => [
                    // call the callback on a specific transition
                    'on' => 'refuses',
                    // will check the ability on the gate or the class policy
                    'can' => 'manage-own-sharing',
                ],
                'guard_on_payment' => [
                    // call the callback on a specific transition
                    'on' => 'pay',
                    // will check the ability on the gate or the class policy
                    'can' => 'can-subscribe',
                ],
                'guard_on_left' => [
                    'on' => 'left',
                    'can' => 'left-subscription',
                ]

                /*'guard_on_submitting' => [
                    // call the callback on a specific transition
                    'on' => 'submit_changes',
                    // will call the method of this class
                    'do' => ['MyClass', 'handle'],
                    // arguments for the callback
                    'args' => ['object'],
                ],
                'guard_on_approving' => [
                    // call the callback on a specific transition
                    'on' => 'approve',
                    // will check the ability on the gate or the class policy
                    'can' => 'approve',
                ],
                */
            ],

            // will be called before applying a transition
            'before' => [],

            // will be called after applying a transition
            'after' => [
                'history' => [
                    'do' => 'StateHistoryManager@storeHistory'
                ]
            ],
        ],
    ]
];
