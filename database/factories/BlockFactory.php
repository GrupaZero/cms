<?php

use Faker\Generator as Faker;
use Gzero\Cms\Models\Block;

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

$factory->define(Block::class, function (Faker $faker) {
    return [
        'type'         => 'basic',
        'region'       => null,
        'theme'        => null,
        'weight'       => $faker->numberBetween(0, 50),
        'filter'       => null,
        'options'      => null,
        'is_active'    => true,
        'is_cacheable' => false,
        'updated_at'   => date('Y-m-d H:i:s')
    ];
});
