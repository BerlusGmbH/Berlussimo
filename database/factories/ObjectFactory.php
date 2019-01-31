<?php

use App\Models\Objekte;
use App\Models\Partner;
use Faker\Generator as Faker;

$factory->define(Objekte::class, function (Faker $faker) {
    return [
        'OBJEKT_KURZNAME' => $faker->regexify("[A-Z]{2}[1-9][0-9]{0,2}")
    ];
});

$factory->afterMakingState(Objekte::class, 'with.owner', function (Objekte $object, Faker $faker) {
    $object->eigentuemer()->associate(factory(Partner::class)->create());
});