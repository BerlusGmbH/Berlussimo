<?php

use App\Models\Details;
use App\Models\Kaufvertraege;
use App\Models\Mietvertraege;
use App\Models\Person;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Person::class, function (Faker $faker) {
    return [
        'name' => $faker->lastName,
        'first_name' => null,
        'birthday' => null,
        'created_at' => Carbon::instance($faker->dateTime())->toDateTimeString(),
        'updated_at' => null,
        'deleted_at' => null
    ];
});

$factory->state(Person::class, 'maximum', function (Faker $faker) {
    return [
        'first_name' => $faker->firstName,
        'birthday' => Carbon::instance($faker->dateTimeBetween('-90 years', 'now'))->toDateString(),
        'updated_at' => $faker->dateTime(),
    ];
});

$factory->afterCreatingState(Person::class, 'maximum', function ($person, $faker) {
    $gender = factory(Details::class)->states('person.gender')->make();
    $person->sexDetail()->save($gender);
    $person->load('sexDetail');
});

$factory->afterCreatingState(Person::class, 'with.note', function ($person, $faker) {
    $person->hinweise()->save(factory(Details::class)->states('person.note')->make());
    $person->load('hinweise');
});

$factory->afterCreatingState(Person::class, 'with.detail', function ($person, $faker) {
    $person->commonDetails()->save(factory(Details::class)->make());
});

$factory->afterCreatingState(Person::class, 'with.rentalContract', function (Person $person, $faker) {
    $rentalContract = factory(Mietvertraege::class)->states('with.object')->create()->refresh();
    $person->mietvertraege()->save($rentalContract);
});

$factory->afterCreatingState(Person::class, 'with.purchaseContract', function (Person $person, $faker) {
    $purchaseContract = factory(Kaufvertraege::class)->states('with.object')->create()->refresh();
    $person->kaufvertraege()->save($purchaseContract);
});