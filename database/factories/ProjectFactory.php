<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\Project::class, function (Faker $faker) {
    return [
        'title' => $faker->text(10),
        'introduce' => $faker->text(100),
        'location' => '成都、北京、天津',
        'start' => $faker->dateTime,
        'end' => $faker->dateTime,
        'points' => $faker->numberBetween(100, 1000),
        'money' => $faker->numberBetween(10, 500),
        'need' => $faker->numberBetween(10, 500),
        'image' => $faker->imageUrl($width=140, $height=200),
        'publisher_id' => $faker->numberBetween(1, 10),
    ];
});