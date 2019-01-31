<?php

use App\Models\Partner;
use Faker\Generator as Faker;

$factory->define(Partner::class, function (Faker $faker) {
    return [
        'PARTNER_NAME' => $faker->company,
        'STRASSE' => $faker->streetName,
        'NUMMER' => $faker->buildingNumber,
        'PLZ' => $faker->postcode,
        'ORT' => $faker->city,
        'LAND' => $faker->country
    ];
});