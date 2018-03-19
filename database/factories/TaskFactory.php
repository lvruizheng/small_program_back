<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Task::class, function (Faker $faker) {
    return [
        'title' => $faker->text(10),
        'introduce' => $faker->text(30),
        'start' => $faker->dateTime,
        'end' => $faker->dateTime,
        'location' => '北京',
        'project_id' => $faker->numberBetween(1, 20)
    ];
});
