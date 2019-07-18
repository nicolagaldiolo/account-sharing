<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Enums\RenewalFrequencies;
use App\RenewalFrequency;
use Faker\Generator as Faker;

$factory->define(RenewalFrequency::class, function (Faker $faker) {
    return [
        'value' => 1,
        'type' => RenewalFrequencies::Months
    ];
});
