<?php namespace Gzero\Core;

use Config;
use Faker\Factory;
use Faker\Generator;
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
        $this->seedContent($contentTypes, $langs, $usersIds);
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
     * Seed content
     *
     * @param array $contentTypes Content type
     * @param array $langs        Array with langs
     * @param array $usersIds     Array with users
     *
     * @throws Exception
     * @return Content
     */
    private function seedContent($contentTypes, $langs, $usersIds)
    {
        $input = [
            [
                'type'              => 'category',
                'weight'            => rand(0, 10),
                'isActive'          => 1,
                'publishedAt'       => date('Y-m-d H:i:s'),
                'translations'      => [
                    'langCode' => 'en',
                    'title'    => 'News',
                    'isActive' => 1
                ],
                'polishTranslation' => [
                    'langCode' => 'pl',
                    'title'    => 'Aktualności',
                    'isActive' => 1
                ]
            ],
            [
                'type'              => 'category',
                'weight'            => rand(0, 10),
                'isActive'          => 1,
                'publishedAt'       => date('Y-m-d H:i:s'),
                'translations'      => $this->prepareContentTranslation($langs['en'], 'Offer', 1),
                'polishTranslation' => $this->prepareContentTranslation($langs['pl'], 'Oferta', 1)
            ],
            [
                'type'              => 'content',
                'weight'            => rand(0, 10),
                'isActive'          => 1,
                'publishedAt'       => date('Y-m-d H:i:s'),
                'translations'      => $this->prepareContentTranslation($langs['en'], 'About us', 1),
                'polishTranslation' => $this->prepareContentTranslation($langs['pl'], 'O nas', 1)
            ]
        ];
        // seed categories
        foreach ($input as $content) {
            $newContent = $this->repository->create($content, $usersIds);
            $this->repository->createTranslation($newContent, $content['polishTranslation']);
            if ($newContent->type == 'category') {
                for ($i = 0; $i < 10; $i++) {
                    // category children
                    $this->seedRandomContent(
                        $contentTypes['content'],
                        $newContent,
                        $langs,
                        $usersIds
                    );
                }
            }
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
            'type'             => $type->name,
            'weight'           => rand(0, 10),
            'rating'           => (bool) rand(0, 10),
            'visits'           => (bool) rand(0, 150),
            'isOnHome'         => (bool) rand(0, 1),
            'isCommentAllowed' => (bool) rand(0, 1),
            'isPromoted'       => (bool) rand(0, 1),
            'isSticky'         => (bool) rand(0, 1),
            'isActive'         => (bool) rand(0, 1),
            'publishedAt'      => date('Y-m-d H:i:s'),
            'translations'     => $this->prepareContentTranslation($langs['en'])
        ];
        if (!empty($parent)) {
            $input['parentId'] = $parent->id;
        }
        $content = $this->repository->create($input, $users);
        $this->repository->createTranslation($content, $this->prepareContentTranslation($langs['pl']));
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

    /**
     * Function generates translation for specified language
     *
     * @param Lang $lang     language of translation
     * @param null $title    optional title value
     * @param null $isActive optional isActive value
     *
     * @return array
     * @throws Exception
     */
    private function prepareContentTranslation(Lang $lang, $title = null, $isActive = null)
    {
        if ($lang) {
            $faker = Factory::create($lang->i18n);
            return [
                'langCode' => $lang->code,
                'title'    => ($title) ? $title : $faker->realText(38, 1),
                'teaser'   => '<p>' . $faker->realText(300) . '</p>',
                'body'     => $this->generateBodyHTML($faker),
                'isActive' => (bool) ($title) ? $isActive : rand(0, 1)
            ];
        }
        throw new Exception("Translation language is required!");
    }

    /**
     * Function generates translation body HTML
     *
     * @param Generator $faker Faker factory
     *
     * @return string generated HTML
     */
    private function generateBodyHTML(Generator $faker)
    {
        $html                   = [];
        $imageCategories        = [
            'abstract',
            'animals',
            'business',
            'cats',
            'city',
            'food',
            'nightlife',
            'fashion',
            'people',
            'nature',
            'sports',
            'technics',
            'transport'
        ];
        $paragraphImageNumber   = rand(0, 5);
        $paragraphHeadingNumber = rand(0, 5);
        $imageUrl               = $faker->imageUrl(1140, 480, $imageCategories[array_rand($imageCategories)]);

        // random dumber of paragraphs
        for ($i = 0; $i < rand(5, 10); $i++) {
            $html[] = '<p>' . $faker->realText(rand(300, 1500)) . '</p>';
            // insert heading
            if ($i == $paragraphHeadingNumber) {
                $html[] = '<h3>' . $faker->realText(100) . '</h3>';
            }
            // insert image
            if ($i == $paragraphImageNumber) {
                $html[] = '<p><img src="' . $imageUrl . '" class="img-responsive"/></p>';
            }
        }
        return implode('', $html);
    }
}
