<?php namespace Gzero\Cms;

use Faker\Factory;
use Faker\Generator;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Models\Block;
use Gzero\Cms\Models\BlockType;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\File;
use Gzero\Cms\Models\FileTranslation;
use Gzero\Cms\Models\FileType;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Cms\Services\BlockService;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\OptionCategory;
use Gzero\Core\Models\User;
use Gzero\Core\Services\LanguageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CMSSeeder extends Seeder {

    const RANDOM_USERS = 12;
    const RANDOM_BLOCKS = 10;
    const RANDOM_FILES = 4;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var BlockService
     */
    protected $blockService;
    /**
     * @var ContentReadRepository
     */
    protected $contentRepository;

    /**
     * CMSSeeder constructor
     *
     * @param BlockService          $blockService      Block service
     * @param ContentReadRepository $contentRepository Content repository
     */
    public function __construct(
        BlockService $blockService,
        ContentReadRepository $contentRepository
    ) {
        $this->faker             = Factory::create();
        $this->contentRepository = $contentRepository;
        $this->blockService      = $blockService;
        // We don't want to allow to pass everything to Eloquent model
        Model::reguard();
    }

    /**
     * This function run all seeds
     *
     * @return void
     */
    public function run()
    {
        $languages = Language::all()->keyBy('code');
        $users     = $this->seedUsers();
        $contents  = $this->seedContent($languages, $users);
        //$blocks       = $this->seedBlock($blockTypes, $languages, $users, $contents);
        //$fileTypes    = $this->seedFileTypes();
        //$files        = $this->seedFiles($fileTypes, $languages, $users, $contents, $blocks);
        //$this->seedOptions($langs);
    }


    /**
     * Seed users
     *
     * @return array
     */
    private function seedUsers()
    {
        factory(User::class, self::RANDOM_USERS)->create();

        return User::all();
    }

    /**
     * Seed content
     *
     * @param array $languages Array with languages
     * @param array $users     Collection with users
     *
     * @throws Exception
     * @return array
     */
    private function seedContent($languages, $users)
    {
        $contents = [];
        $input    = [
            [
                'type'         => 'category',
                'weight'       => rand(0, 10),
                'is_active'    => 1,
                'published_at' => date('Y-m-d H:i:s'),
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'News',
                        'is_active'     => 1
                    ],
                    [
                        'language_code' => 'pl',
                        'title'         => 'Aktualności',
                        'is_active'     => 1
                    ]
                ]
            ],
            [
                'type'         => 'category',
                'weight'       => rand(0, 10),
                'is_active'    => 1,
                'published_at' => date('Y-m-d H:i:s'),
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Offer',
                        'is_active'     => 1
                    ],
                    [
                        'language_code' => 'pl',
                        'title'         => 'Oferta',
                        'is_active'     => 1
                    ]
                ]
            ],
            [
                'type'         => 'content',
                'weight'       => rand(0, 10),
                'is_active'    => 1,
                'published_at' => date('Y-m-d H:i:s'),
                'translations' => [
                    $this->prepareContentTranslation($languages['en'], 'About us', 1),
                    $this->prepareContentTranslation($languages['pl'], 'O nas', 1)
                ]
            ]
        ];
        // seed categories
        foreach ($input as $content) {
            $author = $users->random();
            $data   = array_except($content, ['translations']);
            $en     = head($content['translations']);
            $pl     = last($content['translations']);

            $created = (new CreateContent($data['type'], $en['language_code'], $en['title'], $data, $author))->handle();

            (new AddContentTranslation($created, $pl['language_code'], $pl['title'],
                array_except($pl, ['language_code', 'title'])
            ))->handle();

            //if ($newContent->type == 'category') {
            //    for ($i = 0; $i < 10; $i++) {
            //        // category children
            //        $content = $this->seedRandomContent(
            //            'content',
            //            $newContent,
            //            $languages,
            //            $users
            //        );
            //        // Push to contents array
            //        $contents[$i] = $this->contentRepository->getById($content->id);
            //    }
            //}
        }

        return $contents;
    }

    /**
     * Seed single content
     *
     * @param string       $type      Content type
     * @param Content|Null $parent    Parent element
     * @param array        $languages Array with languages
     * @param array        $users     Array with users
     *
     * @return Content
     */
    private function seedRandomContent(string $type, $parent, $languages, $users)
    {
        $input = [
            'type'               => $type,
            'weight'             => rand(0, 10),
            'rating'             => rand(0, 5),
            'visits'             => rand(0, 150),
            'is_on_home'         => (bool) rand(0, 1),
            'is_comment_allowed' => (bool) rand(0, 1),
            'is_promoted'        => (bool) rand(0, 1),
            'is_sticky'          => (bool) rand(0, 1),
            'is_active'          => (bool) rand(0, 1),
            'published_at'       => date('Y-m-d H:i:s'),
            'translations'       => $this->prepareContentTranslation($languages['en'])
        ];
        if (!empty($parent)) {
            $input['parent_id'] = $parent->id;
        }
        $content = $this->contentRepository->create($input, $users[array_rand($users)]);
        $this->contentRepository->createTranslation($content, $this->prepareContentTranslation($languages['pl']));
        return $content;
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
                'site_name'          => [],
                'site_desc'          => [],
                'default_page_size'  => [],
                'cookies_policy_url' => [],
            ],
            'seo'     => [
                'desc_length'         => [],
                'google_analytics_id' => [],
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
        $blocks = [];
        for ($x = 0; $x < self::RANDOM_BLOCKS; $x++) {
            /** @var TYPE_NAME $contents */
            $block    = $this->seedRandomBlock(
                $blockTypes[array_rand($blockTypes)],
                $langs,
                $users,
                $contents
            );
            $blocks[] = $block;
        }

        return $blocks;
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
            'is_active'    => $isActive,
            'is_cacheable' => $isCacheable,
            'translations' => $this->prepareBlockTranslation($langs['en']),
            'widget'       => [
                'name'         => $this->faker->unique()->word,
                'args'         => array_combine($this->faker->words(), $this->faker->words()),
                'is_active'    => $isActive,
                'is_cacheable' => $isCacheable,
            ],
        ];

        $block = $this->blockService->create($input, $users[array_rand($users)]);
        $this->blockService->createTranslation($block, $this->prepareBlockTranslation($langs['pl']));
        return $block;
    }

    /**
     * Seed file
     *
     * @param array $fileTypes File type
     * @param array $langs     Array with langs
     * @param array $users     Array with users
     * @param array $contents  Array with contents
     * @param array $blocks    Array with blocks
     *
     * @return File
     */
    private function seedFiles($fileTypes, $langs, $users, $contents, $blocks)
    {
        $files = [];
        // seed files for contents
        for ($x = 0; $x < self::RANDOM_FILES; $x++) {
            $file    = $this->seedRandomFiles(
                $fileTypes[array_rand($fileTypes)],
                $langs,
                $users,
                $contents
            );
            $files[] = $file;
        }
        // seed files for blocks
        for ($x = 0; $x < self::RANDOM_FILES; $x++) {
            $file    = $this->seedRandomFiles(
                $fileTypes[array_rand($fileTypes)],
                $langs,
                $users,
                $blocks
            );
            $files[] = $file;
        }

        return $files;
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
            'type'       => $type->name,
            'name'       => $faker->word,
            'extension'  => $faker->fileExtension,
            'size'       => $faker->randomNumber,
            'mime_type'  => $faker->mimeType,
            'info'       => array_combine($this->faker->words(), $this->faker->words()),
            'is_active'  => $isActive,
            'created_by' => $user->id,
        ];
        // create file record in db
        $file = File::create($input);
        // seed all languages translations
        foreach ($langs as $lang) {
            $translation = new FileTranslation();
            $translation->fill($this->prepareFileTranslation($lang));
            $file->translations()->save($translation);
        }
        // add relation to provided entity
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
        $tables = DB::select('SELECT tablename FROM pg_tables WHERE schemaname = \'public\'');
        foreach ($tables as $table) {
            // We don't want to truncate ACL tables too
            if ($table->tablename !== 'migrations' && !str_contains($table->tablename, 'ACL')) {
                DB::statement('TRUNCATE ' . $table->tablename . ' CASCADE;');
            }
        }
    }

    /**
     * Function generates translation for specified language
     *
     * @param Language $lang     language of translation
     * @param null     $title    optional title value
     * @param null     $isActive optional is_active value
     *
     * @return array
     * @throws Exception
     */
    private function prepareContentTranslation(Language $lang, $title = null, $isActive = null)
    {
        if (!$lang) {
            throw new Exception("Translation language is required!");
        }

        $faker = Factory::create($lang->i18n);
        return [
            'language_code'   => $lang->code,
            'title'           => ($title) ? $title : $faker->realText(38, 1),
            'teaser'          => '<p>' . $faker->realText(300) . '</p>',
            'body'            => $this->generateBodyHTML($faker),
            'seo_title'       => $faker->realText(60, 1),
            'seo_description' => $faker->realText(160, 1),
            'is_active'       => (bool) ($title) ? $isActive : rand(0, 1)
        ];
    }

    /**
     * Function generates translation for specified language
     *
     * @param Language $lang     language of translation
     * @param null     $title    optional title value
     * @param null     $isActive optional is_active value
     *
     * @return array
     * @throws Exception
     */
    private function prepareBlockTranslation(Language $lang, $title = null, $isActive = null)
    {
        if ($lang) {
            $faker = Factory::create($lang->i18n);
            return [
                'language_code' => $lang->code,
                'title'         => ($title) ? $title : $faker->realText(38, 1),
                'body'          => $faker->realText(300),
                'custom_fields' => array_combine($this->faker->words(), $this->faker->words()),
                'is_active'     => (bool) ($title) ? $isActive : rand(0, 1)
            ];
        }
        throw new Exception("Translation language is required!");
    }

    /**
     * Function generates translation for specified language
     *
     * @param Language $lang  language of translation
     * @param null     $title optional title value
     *
     * @return array
     * @throws Exception
     */
    private function prepareFileTranslation(Language $lang, $title = null)
    {
        if ($lang) {
            $faker = Factory::create($lang->i18n);
            return [
                'language_code' => $lang->code,
                'title'         => ($title) ? $title : $faker->realText(38, 1),
                'description'   => $faker->realText(300)
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
