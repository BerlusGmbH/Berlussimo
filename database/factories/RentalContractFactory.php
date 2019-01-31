<?php

use App\Models\Einheiten;
use App\Models\Mietvertraege;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Mietvertraege::class, function (Faker $faker) {
    return [
        'MIETVERTRAG_VON' => Carbon::instance($faker->dateTimeBetween('-60 years', 'now'))->toDateString(),
        'MIETVERTRAG_BIS' => '0000-00-00'
    ];
});


$factory->state(Mietvertraege::class, 'ended', function (Faker $faker) {
    $end = Carbon::instance($faker->dateTimeBetween('-30 years', 'now'));
    $start = $end->subSeconds($faker->numberBetween(0, 946080000)); //about 30 years
    return [
        'MIETVERTRAG_VON' => $start->toDateString(),
        'MIETVERTRAG_BIS' => $end->toDateString()
    ];
});

$factory->afterCreatingState(Mietvertraege::class, 'with.object', function (Mietvertraege $rentalContract, Faker $faker) {
    $rentalContract->einheit()->associate(factory(Einheiten::class)->states('with.object')->create());
});