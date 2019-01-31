<?php

use App\Models\Details;
use App\Models\Person;
use Faker\Generator as Faker;

$factory->define(Details::class, function (Faker $faker) {
    return [
        'DETAIL_NAME' => 'Faker',
        'DETAIL_INHALT' => $faker->sentence($nbWords = 10, $variableNbWords = true),
        'DETAIL_BEMERKUNG' => '',
        'DETAIL_ZUORDNUNG_ID' => 0
    ];
});

$factory->state(Details::class, 'person.gender', function (Faker $faker) {
    return [
        'DETAIL_NAME' => 'Geschlecht',
        'DETAIL_INHALT' => rand(0, 1) === 1 ? Person::MALE : Person::FEMALE,
        'DETAIL_BEMERKUNG' => $faker->sentence($nbWords = 10, $variableNbWords = true)
    ];
});

$factory->state(Details::class, 'person.male', function (Faker $faker) {
    return [
        'DETAIL_NAME' => 'Geschlecht',
        'DETAIL_INHALT' => Person::MALE,
        'DETAIL_BEMERKUNG' => $faker->sentence($nbWords = 10, $variableNbWords = true)
    ];
});

$factory->state(Details::class, 'person.female', function (Faker $faker) {
    return [
        'DETAIL_NAME' => 'Geschlecht',
        'DETAIL_INHALT' => Person::FEMALE,
        'DETAIL_BEMERKUNG' => $faker->sentence($nbWords = 10, $variableNbWords = true)
    ];
});

$factory->state(Details::class, 'person.note', function (Faker $faker) {
    return [
        'DETAIL_NAME' => 'Hinweis',
        'DETAIL_INHALT' => $faker->sentence($nbWords = 50, $variableNbWords = true),
        'DETAIL_BEMERKUNG' => rand(0, 1) === 1 ? $faker->sentence($nbWords = 20, $variableNbWords = true) : null
    ];
});

$factory->state(Details::class, 'person.note.minimal', function (Faker $faker) {
    return [
        'DETAIL_NAME' => 'Hinweis',
        'DETAIL_INHALT' => $faker->sentence($nbWords = 50, $variableNbWords = true)
    ];
});

$factory->state(Details::class, 'person.note.maximum', function (Faker $faker) {
    return [
        'DETAIL_NAME' => 'Hinweis',
        'DETAIL_INHALT' => $faker->sentence($nbWords = 50, $variableNbWords = true),
        'DETAIL_BEMERKUNG' => $faker->sentence($nbWords = 20, $variableNbWords = true)
    ];
});