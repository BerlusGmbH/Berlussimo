<?php

use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Objekte;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Einheiten::class, function (Faker $faker) {
    $name = $faker->regexify("[A-Z]{3}[0-9]{1,2}[a-zA-Z]?-[1-6][0-9]{2}");
    $type = $faker->randomElement(Einheiten::TYPES);
    return [
        'EINHEIT_KURZNAME' => $name,
        'EINHEIT_QM' => 0,
        'EINHEIT_LAGE' => '',
        'TYP' => $type
    ];
});

$factory->state(Einheiten::class, 'maximum', function (Faker $faker) {
    $story = $faker->numberBetween(1, 6);
    $name = $faker->regexify("[A-Z]{2}-${story}[0-9]{2}");
    $location = $story === 1 ? "EG " : ($story - 1) . ". OG ";
    $location .= $faker->randomElement(["links", "mitte", "rechts"]);
    $size = $faker->randomFloat(2, 20, 250);
    return [
        'EINHEIT_KURZNAME' => $name,
        'EINHEIT_QM' => $size,
        'EINHEIT_LAGE' => $location,
    ];
});

$factory->state(Einheiten::class, 'with.object', function (Faker $faker) use ($factory) {
    $street = $faker->streetName;
    $parts = [];
    preg_match_all("/\b[[:alnum:]]/i", $street, $parts);
    if (count($parts[0]) < 3) {
        $prefix = Str::substr($street, 0, 3);
        $prefix = Str::upper($prefix);
    } else {
        $prefix = collect($parts[0])->splice(0, 3)->implode('');
    }
    $number = $faker->buildingNumber;
    $objectName = $prefix . $number;
    $story = $faker->numberBetween(1, 6);
    $name = $faker->regexify("${objectName}-${story}[0-9]{2}");
    $location = $story === 1 ? "EG " : ($story - 1) . ". OG ";
    $location .= $faker->randomElement(["links", "mitte", "rechts"]);
    $size = $faker->randomFloat(2, 20, 250);

    $object = factory(Objekte::class)->states('with.owner')->create([
        'OBJEKT_KURZNAME' => $objectName
    ]);

    $house = factory(Haeuser::class)->make([
        'HAUS_STRASSE' => $street,
        'HAUS_NUMMER' => $number,
        'HAUS_STADT' => $faker->city,
        'HAUS_PLZ' => $faker->postcode
    ]);
    $house->objekt()->associate($object);
    $house->save();

    return [
        'EINHEIT_KURZNAME' => $name,
        'EINHEIT_LAGE' => $location,
        'EINHEIT_QM' => $size,
        'TYP' => Einheiten::LIVING_SPACE,
        'HAUS_ID' => $house->id
    ];
});
