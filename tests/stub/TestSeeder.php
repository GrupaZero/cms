<?php

use Faker\Factory;
use Gzero\Cms\Handlers\Content\CategoryHandler;
use Gzero\Cms\Handlers\Content\ContentHandler;
use Gzero\Cms\Models\BlockType;
use Gzero\Cms\Models\ContentType;
use Gzero\Cms\Models\FileType;
use Gzero\Core\Models\Language;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder {

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
        $this->seedLangs();
        $this->seedContentTypes();
        $this->seedBlockTypes();
        $this->seedFileTypes();
    }

    /**
     * Seed langs
     *
     * @return array
     */
    private function seedLangs()
    {
        $langs       = [];
        $langs['en'] = Language::firstOrCreate(
            [
                'code'       => 'en',
                'i18n'       => 'en_US',
                'is_enabled' => true,
                'is_default' => true
            ]
        );

        $langs['pl'] = Language::firstOrCreate(
            [
                'code'       => 'pl',
                'i18n'       => 'pl_PL',
                'is_enabled' => true
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
        $contentTypes   = [];
        $contentTypes[] = ContentType::firstOrCreate([
            'name'    => 'content',
            'handler' => ContentHandler::class
        ]);
        $contentTypes[] = ContentType::firstOrCreate([
            'name'    => 'category',
            'handler' => CategoryHandler::class
        ]);
        return $contentTypes;
    }

    /**
     * Seed block types
     *
     * @return array
     */
    private function seedBlockTypes()
    {
        $blockTypes = [];
        foreach (['basic', 'menu', 'slider'] as $type) {
            $blockTypes[$type] = BlockType::firstOrCreate(['name' => $type]);
        }
        return $blockTypes;
    }

    /**
     * Seed file types
     *
     * @return void
     */
    private function seedFileTypes()
    {
        foreach (['image', 'document', 'video', 'music'] as $type) {
            FileType::firstOrCreate(['name' => $type]);
        }
    }
}
