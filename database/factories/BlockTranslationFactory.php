<?php

use Faker\Generator as Faker;
use Gzero\Cms\Models\BlockTranslation;
use Gzero\Core\Models\Language;

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

$factory->define(BlockTranslation::class, function (Faker $faker) {
    return [
        'language_code' => function () {
            return factory(Language::class)->make()->code;
        },
        'title'         => $faker->realText(38, 1),
        'body'          => $faker->text(),
        'custom_fields' => $faker->realText(60, 1),
        'is_active'     => true,
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s')
    ];
});
