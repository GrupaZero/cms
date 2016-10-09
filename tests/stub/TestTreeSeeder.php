<?php

use Faker\Factory;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Gzero\Entity\User;
use Gzero\Entity\Content;
use Gzero\Repository\ContentRepository;
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
     * @param ContentRepository $content Content repository
     */
    public function __construct(ContentRepository $content)
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
        $this->truncate();
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
     *
     * @return Content
     */
    private function seedContent(ContentType $type, $parent)
    {
        $input = [
            'type'         => $type->name,
            'isActive'     => true,
            'translations' => [
                'langCode' => 'en',
                'title'    => $this->faker->sentence(5),
                'body'     => $this->faker->text(rand(100, 255)),
                'isActive' => true
            ]
        ];
        if (!empty($parent)) {
            $input['parentId'] = $parent->id;
        }
        $content = $this->repository->create($input, User::find(1));
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
                'email'         => 'a@a.pl',
                'firstName'     => 'John',
                'lastName'      => 'Doe',
                'password'      => Hash::make('test'),
                'rememberToken' => true
            ]
        );
        return $user;
    }


    /**
     * Truncate database
     */
    private function truncate()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables             = DB::select('SHOW TABLES');
        $tables_in_database = "Tables_in_" . Config::get('database.connections.testbench.database');
        foreach ($tables as $table) {
            if ($table->$tables_in_database !== 'migrations') {
                DB::table($table->$tables_in_database)->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
