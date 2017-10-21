<?php

use Faker\Factory;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Gzero\Entity\User;
use Gzero\Entity\Content;
use Gzero\Repository\ContentService;
use Illuminate\Database\Seeder;

/**
 * Class DummyValidator
 */
class TestTreeSeeder extends Seeder {


    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * CMSSeeder constructor
     *
     * @param ContentService $content Content repository
     */
    public function __construct(ContentService $content)
    {
        $this->faker      = Factory::create();
        $this->repository = $content;
        Content::reguard();
    }

    /**
     * This function run all seeds
     *
     * @return void
     * @SuppressWarnings("PHPMD")
     */
    public function run()
    {
        $this->seedUsers();
        $this->seedLangs();
        $contentTypes = $this->seedContentTypes();

        // Seed content trees
        $categories    = [];
        $subCategories = [];
        // Root categories
        for ($i = 0; $i < 2; $i++) {
            $categories[] = $this->seedContent(
                $contentTypes['category'],
                null
            );
        }
        // first root categories
        for ($i = 0; $i < 1; $i++) {
            $subCategories[] = $this->seedContent(
                $contentTypes['category'],
                $categories[0]
            );
        }
        for ($i = 0; $i < 1; $i++) { // Content in categories
            $this->seedContent(
                $contentTypes['content'],
                $this->faker->randomElement($categories)
            );
        }
        for ($i = 0; $i < 1; $i++) { // Content in sub categories
            $this->seedContent(
                $contentTypes['content'],
                $this->faker->randomElement($subCategories)
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
                'code'       => 'en',
                'i18n'       => 'en_US',
                'is_enabled' => true,
                'is_default' => true
            ]
        );

        $langs['pl'] = Lang::firstOrCreate(
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
        $contentTypes = [];
        foreach (['content', 'category'] as $type) {
            $contentTypes[$type] = ContentType::firstOrCreate(['name' => $type, 'is_active' => true]);
        }
        return $contentTypes;
    }

    /**
     * Seed single content
     *
     * @param ContentType  $type   Content type
     * @param Content|Null $parent Parent element
     *
     * @return Content
     */
    private function seedContent(ContentType $type, $parent)
    {
        $input = [
            'type'         => $type->name,
            'is_active'    => true,
            'translations' => [
                'lang_code' => 'en',
                'title'     => $this->faker->sentence(5),
                'body'      => $this->faker->text(rand(100, 255)),
                'is_active' => true
            ]
        ];
        if (!empty($parent)) {
            $input['parent_id'] = $parent->id;
        }
        $content = $this->repository->create($input, User::find(1));
        return $content;
    }

    /**
     * Seed users
     *
     * @return User
     */
    private function seedUsers()
    {
        // Create user
        $user = User::where('email', '=', 'a@a.pl')->first();
        if (!$user) {
            $user = User::create(
                [
                    'email'         => 'a@a.pl',
                    'first_name'    => 'John',
                    'last_name'     => 'Doe',
                    'password'      => 'test',
                    'rememberToken' => true
                ]
            );
        }
        return $user;
    }
}
