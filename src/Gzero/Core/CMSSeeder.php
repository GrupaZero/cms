<?php namespace Gzero\Core;

use Config;
use Faker\Factory;
use Gzero\Entity\Content;
use Gzero\Entity\ContentType;
use Gzero\Entity\ContentTranslation;
use Gzero\Entity\Lang;
use Gzero\Entity\Route;
use Gzero\Entity\RouteTranslation;
use Gzero\Entity\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class CMSSeeder
 *
 * @package    Gzero\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @SuppressWarnings("PHPMD")
 */
class CMSSeeder extends Seeder {

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * CMSSeeder constructor
     */
    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * This function run all seeds
     *
     * @return void
     * @SuppressWarnings("PHPMD")
     */
    public function run()
    {
        $this->truncate();
        $langs        = $this->seedLangs();
        $contentTypes = $this->seedContentTypes();
        $usersIds     = $this->seedUsers();
        $contents     = [];
        $categories   = [];
        for ($i = 0; $i < 12; $i++) { // Categories
            $categories[] = $this->seedContent($contentTypes['category'], null, $langs, $usersIds); // Content without category
            $categories[] = $this->seedContent(
                $contentTypes['category'],
                $this->faker->randomElement($categories),
                $langs,
                $usersIds
            );
        }
        for ($i = 0; $i < 20; $i++) { // Content in categories
            $contents[] = $this->seedContent($contentTypes['content'], null, $langs, $usersIds); // Content without category
            $contents[] = $this->seedContent(
                $contentTypes['content'],
                $this->faker->randomElement($categories),
                $langs,
                $usersIds
            );
        }
    }

    /**
     * Seed langs
     *
     * @return array
     */
    private function seedLangs()
    {
        $langs       = [];
        $langs['en'] = Lang::find('en');
        if (empty($langs['en'])) {
            $langs['en'] = new Lang(
                [
                    'code'      => 'en',
                    'i18n'      => 'en_US',
                    'isEnabled' => true,
                    'isDefault' => true
                ]
            );
            $langs['en']->save();
        }

        $langs['pl'] = Lang::find('pl');
        if (empty($langs['pl'])) {
            $langs['pl'] = new Lang(
                [
                    'code'      => 'pl',
                    'i18n'      => 'pl_PL',
                    'isEnabled' => true
                ]
            );
            $langs['pl']->save();
        }
        return $langs;
    }

    /**
     * Seed content types
     *
     * @return array
     */
    private function seedContentTypes()
    {
        $contentTypes = [];
        foreach (['content', 'category'] as $type) {
            $contentTypes[$type] = ContentType::firstOrCreate(['name' => $type, 'isActive' => true]);
        }
        return $contentTypes;
    }

    /**
     * Seed single content
     *
     * @param ContentType  $type   Content type
     * @param Content|Null $parent Parent element
     * @param array        $langs  Array with langs
     * @param array        $users  Array with users
     *
     * @return Content
     */
    private function seedContent(ContentType $type, $parent, $langs, $users)
    {
        $content         = new Content(
            [
                'type'     => $type->name,
                'authorId' => $this->faker->randomElement($users),
                'isActive' => (bool) rand(0, 1)
            ]
        );
        $content->weight = rand(0, 10);
        if ($parent) {
            $content->setChildOf($parent);
        } else {
            $content->setAsRoot();
        }
        $route = new Route(['isActive' => 1]);
        $content->route()->save($route);
        foreach ($langs as $key => $value) {
            $translation           = new ContentTranslation(['langCode' => $key]);
            $translation->title    = $this->faker->sentence(5);
            $translation->body     = $this->faker->text(255);
            $translation->isActive = true;
            $content->translations()->save($translation);
            $routeTranslation = new RouteTranslation(
                [
                    'langCode' => $key,
                    'url'      => $this->faker->unique()->word,
                    'isActive' => true
                ]
            );
            $route->translations()->save($routeTranslation);
        }
        return $content;
    }

    /**
     * Seed users
     *
     * @return array
     */
    private function seedUsers()
    {
        // Create user
        $user = User::find(1);
        if (!$user) {
            $user = User::create(
                [
                    'email'     => 'a@a.pl',
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                    'password'  => Hash::make('test')

                ]
            );
        }
        return [null, $user->id];
    }

    /**
     * Truncate database
     *
     * @return void
     */
    private function truncate()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables             = DB::select('SHOW TABLES');
        $tables_in_database = "Tables_in_" . Config::get('database.connections.mysql.database');
        foreach ($tables as $table) {
            if ($table->$tables_in_database !== 'migrations') {
                DB::table($table->$tables_in_database)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
