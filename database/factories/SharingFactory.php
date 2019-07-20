<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Sharing;
use App\Enums\SharingVisibility;
use Faker\Generator as Faker;

$factory->define(Sharing::class, function (Faker $faker) {

    return [
        'name' => $faker->name,
        'description' => $faker->sentence,
        'visibility' => array_rand(SharingVisibility::getValues()),
        'capacity' => $faker->numberBetween(1,5),
        'price' => $faker->randomFloat(2, 0, 20),
        'image' => $faker->imageUrl(),
        'category_id' => function(){
            return factory(\App\Category::class)->create();
        },
        'owner_id' => function(){
            return factory(\App\User::class)->create();
        }

    ];
});