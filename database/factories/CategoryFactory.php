<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->sentence,
        'price' => $faker->randomFloat(2, 10, 30),
        'image' => $faker->imageUrl(),
        'capacity' => $faker->numberBetween(1,5),
        'customizable' => $faker->boolean(20)
    ];
});
