<?php

use Faker\Generator as Faker;
use Gzero\Cms\Models\ContentTranslation;

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

$factory->define(ContentTranslation::class, function (Faker $faker) {
    return [
        'language_code'   => null,
        'title'           => $faker->realText(38, 1),
        'teaser'          => $faker->realText(300),
        'body'            => $faker->text(),
        'seo_title'       => $faker->realText(60, 1),
        'seo_description' => $faker->realText(160, 1),
        'is_active'       => true
    ];
});
