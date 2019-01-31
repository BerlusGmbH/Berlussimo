<?php

use App\Models\Einheiten;
use App\Models\Kaufvertraege;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Kaufvertraege::class, function (Faker $faker) {
    return [
        'VON' => Carbon::instance($faker->dateTimeBetween('-60 years', 'now'))->toDateString(),
        'BIS' => '0000-00-00'
    ];
});


$factory->state(Kaufvertraege::class, 'ended', function (Faker $faker) {
    $end = Carbon::instance($faker->dateTimeBetween('-30 years', 'now'));
    $start = $end->subSeconds($faker->numberBetween(0, 946080000)); //about 30 years
    return [
        'VON' => $start->toDateString(),
        'BIS' => $end->toDateString()
    ];
});

$factory->afterCreatingState(Kaufvertraege::class, 'with.object', function (Kaufvertraege $purchaseContract, Faker $faker) {
    $purchaseContract->einheit()->associate(factory(Einheiten::class)->states('with.object')->create());
});