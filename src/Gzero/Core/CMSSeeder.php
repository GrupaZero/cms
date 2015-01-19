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
        $this->faker->addProvider(new \Faker\Provider\en_US\Text($this->faker));
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
        $categories   = $this->seedContent();
        //for ($i = 0; $i < 12; $i++) { // Categories
        //    // Content without category
        //    $categories[] = $this->seedRandomContent($contentTypes['category'], null, $langs, $usersIds);
        //    $categories[] = $this->seedRandomContent(
        //        $contentTypes['category'],
        //        $this->faker->randomElement($categories),
        //        $langs,
        //        $usersIds
        //    );
        //}
        //for ($i = 0; $i < 10; $i++) { // Content in categories
        //    $contents[] = $this->seedRandomContent($contentTypes['content'], null, $langs, $usersIds); // Content without category
        //    $contents[] = $this->seedRandomContent(
        //        $contentTypes['content'],
        //        $this->faker->randomElement($categories),
        //        $langs,
        //        $usersIds
        //    );
        //}
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
     * Seed all custom categories
     *
     * @return array
     */
    private function seedContent()
    {
        $input = [
            [
                'type'              => 'category',
                'weight'            => rand(0, 10),
                'isActive'          => 1,
                'translations'      => [
                    'langCode' => 'en',
                    'title'    => 'News',
                    'body'     => $this->faker->text(rand(100, 255)),
                    'isActive' => 1
                ],
                'polishTranslation' => [
                    'langCode' => 'pl',
                    'title'    => 'Aktualności',
                    'body'     => $this->faker->text(rand(100, 255)),
                    'isActive' => 1
                ]
            ],
            [
                'type'              => 'category',
                'weight'            => rand(0, 10),
                'isActive'          => 1,
                'translations'      => [
                    'langCode' => 'en',
                    'title'    => 'Offer',
                    'body'     => $this->faker->text(rand(100, 255)),
                    'isActive' => 1
                ],
                'polishTranslation' => [
                    'langCode' => 'pl',
                    'title'    => 'Oferta',
                    'body'     => $this->faker->text(rand(100, 255)),
                    'isActive' => 1
                ]
            ],
            [
                'type'              => 'content',
                'weight'            => rand(0, 10),
                'isActive'          => 1,
                'translations'      => [
                    'langCode' => 'en',
                    'title'    => 'About Us',
                    'body'     => $this->faker->text(500),
                    'isActive' => 1
                ],
                'polishTranslation' => [
                    'langCode' => 'pl',
                    'title'    => 'O nas',
                    'body'     => $this->faker->paragraph(3),
                    'isActive' => 1
                ]
            ]
        ];
        // seed categories
        foreach ($input as $content) {
            $newContent = $this->repository->create($content, User::find(1));
            $this->repository->createTranslation($newContent, $content['polishTranslation']);
        }
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
    private function seedRandomContent(ContentType $type, $parent, $langs, $users)
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
