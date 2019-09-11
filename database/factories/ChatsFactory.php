<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Chats;
use Faker\Generator as Faker;

$factory->define(Chats::class, function (Faker $faker) {
    return [
        'message' => $faker->sentence,
        'sharing_id' => function(){
            return factory(\App\Sharing::class)->create();
        },
        'user_id' => function(){
            return factory(\App\User::class)->create();
        }
    ];
});
