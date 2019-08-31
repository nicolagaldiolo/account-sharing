<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Renewal;
use Faker\Generator as Faker;

$factory->define(Renewal::class, function (Faker $faker) {

    return [
        'status' => \App\Enums\RenewalStatus::Confirmed,
        'sharing_user_id' => function(){
            return factory(\App\SharingUser::class)->create()->id;
        },
        'starts_at' => $faker->dateTimeBetween($startDate = '-30 days', $endDate = 'now'),
        'expires_at' => $faker->dateTimeBetween($startDate = 'now', $endDate = '+30 days')
    ];
});