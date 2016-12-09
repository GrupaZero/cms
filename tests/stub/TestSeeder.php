<?php

use Faker\Factory;
use Gzero\Entity\BlockType;
use Gzero\Entity\ContentType;
use Gzero\Entity\FileType;
use Gzero\Entity\Lang;
use Illuminate\Database\Seeder;
use Gzero\Repository\UserRepository;

/**
 * Class DummyValidator
 */
class TestSeeder extends Seeder {

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var UserRepository
     */
    protected $userRepo;

    /**
     * CMSSeeder constructor
     */
    public function __construct(UserRepository $user)
    {
        $this->faker    = Factory::create();
        $this->userRepo = $user;
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
        $langs['en'] = Lang::firstOrCreate(
            [
                'code'      => 'en',
                'i18n'      => 'en_US',
                'is_enabled' => true,
                'is_default' => true
            ]
        );

        $langs['pl'] = Lang::firstOrCreate(
            [
                'code'      => 'pl',
                'i18n'      => 'pl_PL',
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
        $contentTypes = [];
        foreach (['content', 'category'] as $type) {
            $contentTypes[$type] = ContentType::firstOrCreate(['name' => $type, 'is_active' => true]);
        }
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
        foreach (['basic', 'menu', 'slider', 'widget', 'content'] as $type) {
            $blockTypes[$type] = BlockType::firstOrCreate(['name' => $type, 'is_active' => true]);
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
            FileType::firstOrCreate(['name' => $type, 'is_active' => true]);
        }
    }
}
