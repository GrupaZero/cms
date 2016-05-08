<?php namespace Gzero\Core;

use Config;
use Faker\Factory;
use Faker\Generator;
use Gzero\Entity\Block;
use Gzero\Entity\BlockType;
use Gzero\Entity\Content;
use Gzero\Entity\ContentType;
use Gzero\Entity\File;
use Gzero\Entity\FileTranslation;
use Gzero\Entity\FileType;
use Gzero\Entity\Lang;
use Gzero\Entity\OptionCategory;
use Gzero\Entity\User;
use Gzero\Repository\BlockRepository;
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

    const RANDOM_USERS = 12;
    const RANDOM_BLOCKS = 2;
    const RANDOM_FILES = 1;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * @var BlockRepository
     */
    protected $blockRepository;

    /**
     * CMSSeeder constructor
     *
     * @param ContentRepository $contentRepository Content repository
     * @param BlockRepository   $blockRepository   Block repository
     */
    public function __construct(ContentRepository $contentRepository, BlockRepository $blockRepository)
    {
        $this->faker             = Factory::create();
        $this->contentRepository = $contentRepository;
        $this->blockRepository   = $blockRepository;
    }

    /**
     * This function run all seeds
     *
     * @return void
     */
    public function run()
    {
        $this->truncate();
        $langs        = $this->seedLangs();
        $contentTypes = $this->seedContentTypes();
        $blockTypes   = $this->seedBlockTypes();
        $users        = $this->seedUsers();
        $contents     = $this->seedContent($contentTypes, $langs, $users);
        $blocks       = $this->seedBlock($blockTypes, $langs, $users, $contents);
        $fileTypes    = $this->seedFileTypes();
        $files        = $this->seedFiles($fileTypes, $langs, $users, $contents, $blocks);
        $this->seedOptions($langs);
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

        $langs['de'] = Lang::firstOrCreate(
            [
                'code'      => 'de',
                'i18n'      => 'de_DE',
                'isEnabled' => false
            ]
        );

        $langs['fr'] = Lang::firstOrCreate(
            [
                'code'      => 'fr',
                'i18n'      => 'fr_FR',
                'isEnabled' => false
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
     * @param array $users        Array with users
     *
     * @throws Exception
     * @return Content
     */
    private function seedContent($contentTypes, $langs, $users)
    {
        $contents = [];
        $input    = [
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
                ],
            ],
            [
                'type'              => 'category',
                'weight'            => rand(0, 10),
                'isActive'          => 1,
                'publishedAt'       => date('Y-m-d H:i:s'),
                'translations'      => [
                    'langCode' => 'en',
                    'title'    => 'Offer',
                    'isActive' => 1
                ],
                'polishTranslation' => [
                    'langCode' => 'pl',
                    'title'    => 'Oferta',
                    'isActive' => 1
                ]
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
            $newContent = $this->contentRepository->create($content, $users[array_rand($users)]);
            $this->contentRepository->createTranslation($newContent, $content['polishTranslation']);
            if ($newContent->type == 'category') {
                for ($i = 0; $i < 10; $i++) {
                    // category children
                    $content = $this->seedRandomContent(
                        $contentTypes['content'],
                        $newContent,
                        $langs,
                        $users
                    );
                    // Push to contents array
                    $contents[$i] = $this->contentRepository->getById($content->id);
                }
            }
        }

        return $contents;
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
            'rating'           => rand(0, 5),
            'visits'           => rand(0, 150),
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
        $content = $this->contentRepository->create($input, $users[array_rand($users)]);
        $this->contentRepository->createTranslation($content, $this->prepareContentTranslation($langs['pl']));
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
                'email'     => 'admin@gzero.pl',
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'password'  => Hash::make('test')

            ]
        );

        $user->isAdmin = 1;

        $user->save();

        $users = [$user];
        for ($x = 0; $x < self::RANDOM_USERS; $x++) {
            $user    = User::firstOrCreate(
                [
                    'email'     => $this->faker->email,
                    'firstName' => $this->faker->firstName,
                    'lastName'  => $this->faker->lastName,
                    'password'  => Hash::make($this->faker->word)
                ]
            );
            $users[] = $user;
        }

        return $users;
    }

    /**
     * Seed options from gzero config to 'main' category
     *
     * @param Lang $langs translations languages
     *
     * @return void
     */
    private function seedOptions($langs)
    {
        // gzero config options
        $options = [
            'general' => [
                'siteName'         => [],
                'siteDesc'         => [],
                'defaultPageSize'  => [],
                'cookiesPolicyUrl' => [],
            ],
            'seo'     => [
                'seoDescLength'     => [],
                'googleAnalyticsId' => [],
            ]
        ];

        // Propagate Lang options based on gzero config
        foreach ($options as $categoryKey => $category) {
            foreach ($options[$categoryKey] as $key => $option) {
                foreach ($langs as $code => $lang) {
                    $options[$categoryKey][$key][$code] = config('gzero.' . $key);
                }
            }
        }

        // Seed options
        foreach ($options as $category => $option) {
            OptionCategory::create(['key' => $category]);
            foreach ($option as $key => $value) {
                OptionCategory::find($category)->options()->create(
                    ['key' => $key, 'value' => $value]
                );
            }
        }
    }

    /**
     * Seed block types
     *
     * @return array
     */
    private function seedBlockTypes()
    {
        $blockTypes = [];
        foreach (['basic', 'menu', 'slider', 'content', 'widget'] as $type) {
            $blockTypes[$type] = BlockType::firstOrCreate(['name' => $type, 'isActive' => true]);
        }
        return $blockTypes;
    }


    /**
     * Seed block
     *
     * @param array $blockTypes Block type
     * @param array $langs      Array with langs
     * @param array $users      Array with users
     * @param array $contents   Array with contents
     *
     * @return Block
     */
    private function seedBlock($blockTypes, $langs, $users, $contents)
    {
        for ($x = 0; $x < self::RANDOM_BLOCKS; $x++) {
            /** @var TYPE_NAME $contents */
            $this->seedRandomBlock(
                $blockTypes[array_rand($blockTypes)],
                $langs,
                $users,
                $contents
            );
        }
    }

    /**
     * Seed single block
     *
     * @param BlockType $type     Block type
     * @param array     $langs    Array with langs
     * @param array     $users    Array with users
     * @param array     $contents Array with contents
     *
     * @return Block
     */
    private function seedRandomBlock(BlockType $type, $langs, $users, $contents)
    {
        $isActive    = (bool) rand(0, 1);
        $isCacheable = (bool) rand(0, 1);
        $filter      = (rand(0, 1)) ? [
            '+' => [$this->getRandomBlockFilter($contents)],
            '-' => [$this->getRandomBlockFilter($contents)]
        ] : null;
        $input       = [
            'type'         => $type->name,
            'region'       => $this->getRandomBlockRegion(),
            'weight'       => rand(0, 12),
            'filter'       => $filter,
            'options'      => array_combine($this->faker->words(), $this->faker->words()),
            'isActive'     => $isActive,
            'isCacheable'  => $isCacheable,
            'translations' => $this->prepareBlockTranslation($langs['en']),
            'widget'       => [
                'name'        => $this->faker->unique()->word,
                'args'        => array_combine($this->faker->words(), $this->faker->words()),
                'isActive'    => $isActive,
                'isCacheable' => $isCacheable,
            ],
        ];

        $block = $this->blockRepository->create($input, $users[array_rand($users)]);
        $this->blockRepository->createTranslation($block, $this->prepareBlockTranslation($langs['pl']));
        return $block;
    }

    /**
     * Seed block types
     *
     * @return array
     */
    private function seedFileTypes()
    {
        $fileTypes = [];
        foreach (['image', 'document', 'video', 'music'] as $type) {
            $fileTypes[$type] = FileType::firstOrCreate(['name' => $type, 'isActive' => true]);
        }
        return $fileTypes;
    }

    /**
     * Seed file
     *
     * @param array $fileTypes File type
     * @param array $langs     Array with langs
     * @param array $users     Array with users
     * @param array $contents  Array with contents
     * @param array $blocks    Array with contents
     *
     * @return File
     */
    private function seedFiles($fileTypes, $langs, $users, $contents, $blocks)
    {
        // seed files for contents
        for ($x = 0; $x < self::RANDOM_FILES; $x++) {
            $this->seedRandomFiles(
                $fileTypes[array_rand($fileTypes)],
                $langs,
                $users,
                $contents
            );
        }

        //// seed files for blocks
        //for ($x = 0; $x < self::RANDOM_FILES; $x++) {
        //    $this->seedRandomFiles(
        //        $fileTypes[array_rand($fileTypes)],
        //        $langs,
        //        $users,
        //        $blocks
        //    );
        //}
    }

    /**
     * Seed single file
     *
     * @param FileType $type   File type
     * @param array    $langs  Array with langs
     * @param array    $users  Array with users
     * @param array    $entity Array with entities to attach file to
     *
     * @return File
     */
    private function seedRandomFiles(FileType $type, $langs, $users, $entity)
    {
        $isActive = (bool) rand(0, 1);
        $faker    = Factory::create($langs['en']->i18n);
        $user     = $users[array_rand($users)];
        $entity   = $entity[array_rand($entity)];
        $input    = [
            'type'      => $type->name,
            'name'      => $faker->word,
            'extension' => $faker->fileExtension,
            'size'      => $faker->fileExtension,
            'mimeType'  => $faker->mimeType,
            'info'      => array_combine($this->faker->words(), $this->faker->words()),
            'isActive'  => $isActive,
            'createdBy' => $user->id,
        ];

        $file        = File::firstOrCreate($input);
        $translation = new FileTranslation();
        $translation->fill($this->prepareFileTranslation($langs['en']));
        $file->translations()->save($translation);
        $entity->files()->attach($file->id, ['weight' => rand(0, 10)]);

        return $file;
    }

    /**
     * Truncate database
     *
     * @return void
     */
    private function truncate()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables           = DB::select('SHOW TABLES');
        $tablesInDatabase = "Tables_in_" . config('database.connections.mysql.database');
        foreach ($tables as $table) {
            if ($table->$tablesInDatabase !== 'migrations') {
                DB::table($table->$tablesInDatabase)->truncate();
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
                'langCode'       => $lang->code,
                'title'          => ($title) ? $title : $faker->realText(38, 1),
                'teaser'         => '<p>' . $faker->realText(300) . '</p>',
                'body'           => $this->generateBodyHTML($faker),
                'seoTitle'       => $faker->realText(60, 1),
                'seoDescription' => $faker->realText(160, 1),
                'isActive'       => (bool) ($title) ? $isActive : rand(0, 1)
            ];
        }
        throw new Exception("Translation language is required!");
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
    private function prepareBlockTranslation(Lang $lang, $title = null, $isActive = null)
    {
        if ($lang) {
            $faker = Factory::create($lang->i18n);
            return [
                'langCode'     => $lang->code,
                'title'        => ($title) ? $title : $faker->realText(38, 1),
                'body'         => $faker->realText(300),
                'customFields' => array_combine($this->faker->words(), $this->faker->words()),
                'isActive'     => (bool) ($title) ? $isActive : rand(0, 1)
            ];
        }
        throw new Exception("Translation language is required!");
    }

    /**
     * Function generates translation for specified language
     *
     * @param Lang $lang  language of translation
     * @param null $title optional title value
     *
     * @return array
     * @throws Exception
     */
    private function prepareFileTranslation(Lang $lang, $title = null)
    {
        if ($lang) {
            $faker = Factory::create($lang->i18n);
            return [
                'langCode'    => $lang->code,
                'title'       => ($title) ? $title : $faker->realText(38, 1),
                'description' => $faker->realText(300)
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

    /**
     * Function generates block filter path
     *
     * @param array $contents Array with contents
     *
     * @return string
     */
    private function getRandomBlockFilter($contents)
    {
        return rand(0, 1) ? $contents[array_rand($contents)]->path . '*' : $contents[array_rand($contents)]->path;
    }

    /**
     * Function returns one of the available block regions
     *
     * @return string
     */
    private function getRandomBlockRegion()
    {
        $availableRegions = config('gzero.available_blocks_regions');
        return $availableRegions[array_rand($availableRegions, 1)];
    }
}
