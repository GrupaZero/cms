<?php namespace Gzero\Core;

use Config;
use Faker\Factory;
use Gzero\Entity\Content;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Gzero\Entity\User;
use Gzero\Repository\ContentRepository;
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
     *
     * @param ContentRepository $content Content repository
     */
    public function __construct(ContentRepository $content)
    {
        $this->faker      = Factory::create();
        $this->repository = $content;
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
        for ($i = 0; $i < 10; $i++) { // Content in categories
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
        $langs['en'] = Lang::firstOrCreate(
            [
                'code'      => 'en',
                'i18n'      => 'en_US',
                'isEnabled' => true,
                'isDefault' => true
            ]
        );

        $langs['pl'] = Lang::firstOrCreate(
            [
                'code'      => 'pl',
                'i18n'      => 'pl_PL',
                'isEnabled' => true
            ]
        );
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
        $input = [
            'type'     => $type->name,
            'weight'   => rand(0, 10),
            'isActive' => (bool) rand(0, 1)
        ];
        if (!empty($parent)) {
            $input['parentId'] = $parent->id;
        }
        $translations = [];
        foreach ($langs as $key => $value) {
            $input['translations'] = [
                'langCode' => $key,
                'title'    => $this->faker->sentence(5),
                'body'     => $this->faker->text(rand(100, 255)),
                'isActive' => (bool) rand(0, 1)
            ];
            $translations[$key]    = $input['translations'];
        }
        $content = $this->repository->create($input, User::find(1));
        foreach ($translations as $value) {
            $this->repository->createTranslation($content, $value);
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
        $user = User::firstOrCreate(
            [
                'email'     => 'a@a.pl',
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'password'  => Hash::make('test')

            ]
        );
        return $user;
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
