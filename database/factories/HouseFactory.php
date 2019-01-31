<?php

use App\Models\Haeuser;
use Faker\Generator as Faker;

$factory->define(Haeuser::class, function (Faker $faker) {
    return [
        'HAUS_STRASSE' => $faker->streetName,
        'HAUS_NUMMER' => $faker->buildingNumber,
        'HAUS_STADT' => $faker->city,
        'HAUS_PLZ' => $faker->postcode,
        'HAUS_QM' => 0
    ];
});