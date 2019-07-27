<?php

return [
    'sharing' => [
        // class of your domain object
        'class' => \App\Sharing::class,

        // name of the graph (default is "default")
        'graph' => 'sharing',

        // property of your object holding the actual state (default is "state")
        'property_path' => 'pivot.status',

        //'metadata' => [
        //    'title' => 'Graph A',
        //],

        'states' => \App\Enums\SharingStatus::getValues(),

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
            ],
            'refuses' => [
                'from' =>  [\App\Enums\SharingStatus::Pending, \App\Enums\SharingStatus::Approved],
                'to' => \App\Enums\SharingStatus::Refused,
            ],
            'join' => [
                'from' => [\App\Enums\SharingStatus::Approved],
                'to' => \App\Enums\SharingStatus::Joined,
            ]
        ],

        // list of all callbacks
        /*'callbacks' => [
            // will be called when testing a transition
            'guard' => [
                'guard_on_submitting' => [
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
            ],

            // will be called before applying a transition
            'before' => [],

            // will be called after applying a transition
            'after' => [],
        ],
        */
    ],
];
