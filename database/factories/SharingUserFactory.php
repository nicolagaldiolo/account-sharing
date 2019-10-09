<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\SharingUser;
use Faker\Generator as Faker;

$factory->define(SharingUser::class, function (Faker $faker) {

    logger($faker->dateTime());

    return [
        'sharing_id' => function(){
            return factory(\App\Sharing::class)->create();
        },
        'user_id' => function(){
            return factory(\App\User::class)->create();
        },
        'status' => \App\Enums\SharingStatus::Pending,
        'credential_updated_at' => $faker->now()
    ];
});
