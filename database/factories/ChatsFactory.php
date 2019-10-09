<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Chat;
use Faker\Generator as Faker;

$factory->define(Chat::class, function (Faker $faker) {
    $date = $faker->dateTimeBetween('-5 days');
    return [
        'message' => $faker->sentence,
        'sharing_id' => function(){
            return factory(\App\Sharing::class)->create();
        },
        'user_id' => function(){
            return factory(\App\User::class)->create();
        },
        'created_at' => $date->format('Y-m-d H:i:s'),
        'updated_at' => $date->format('Y-m-d H:i:s')
    ];
});
