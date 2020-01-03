<?php

use App\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {

    $countries = collect(config('custom.countries'));
    $country_key = $countries->keys()->random();
    $country = $countries->get($country_key);

    return [
        'name' => $faker->firstName,
        'surname' => $faker->lastName,
        'birthday' => $faker->dateTimeBetween($startDate = '-50 years', $endDate = '-13 years'), // Stripe min 13years old
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'phone' => $country['fake_data']['number'],
        'address' => $country['fake_data']['address'],
        'country' => $country_key,
        'currency' => $country['currency'],
        'tos_acceptance_at' => Carbon\Carbon::now(),
        'remember_token' => Str::random(10),
    ];
});
