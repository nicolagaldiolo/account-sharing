<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Credential;
use Faker\Generator as Faker;

$factory->define(Credential::class, function (Faker $faker) {
    return [
        'username' => $faker->username,
        'password' => $faker->password,
    ];
});
