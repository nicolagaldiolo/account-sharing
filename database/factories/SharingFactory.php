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
        'status' => \App\Enums\SharingApprovationStatus::Approved,
        'slot' => ($faker->numberBetween(2,6)) - 1,
        'price' => $faker->randomFloat(2, 0, 20),
        'multiaccount' => 0,
        'image' => $faker->imageUrl(),
        'renewal_frequency_id' => function(){
            return factory(\App\RenewalFrequency::class)->create();
        },
        'category_id' => function(){
            return factory(\App\Category::class)->create();
        },
        'owner_id' => function() {
            return factory(\App\User::class)->create();
        }
    ];
});
