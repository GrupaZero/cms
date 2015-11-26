<?php

use Faker\Factory;
use Gzero\Entity\BlockType;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Gzero\Entity\User;
use Gzero\Entity\Option;
use Gzero\Entity\OptionCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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
        $this->truncate();
        $this->seedLangs();
        $this->seedContentTypes();
        $this->seedBlockTypes();
        $this->seedUsers();
        $this->seedOptions();
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
     * Seed block types
     *
     * @return array
     */
    private function seedBlockTypes()
    {
        $blockTypes = [];
        foreach (['basic', 'menu', 'slider'] as $type) {
            $blockTypes[$type] = BlockType::firstOrCreate(['name' => $type, 'isActive' => true]);
        }
        return $blockTypes;
    }

    /**
     * Seed users
     *
     * @return array
     */
    private function seedUsers()
    {
        $users = [];

        // Create users
        $users[] = User::firstOrCreate(
            [
                'email'     => 'a@a.pl',
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'password'  => Hash::make('test')

            ]
        );

        $users[] = User::firstOrCreate(
            [
                'email'     => 'b@b.pl',
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'password'  => Hash::make('test123')

            ]
        );

        return $users;
    }

    const CATEGORY_MAIN = 'main';
    const OPTION_STANDARD = 'standard';
    const OPTION_STANDARD2 = 'standard2';
    const OPTION_VALUE_STANDARD = 'cisco';
    const OPTION_VALUE_STANDARD2 = 'microsoft';

    private function seedOptions()
    {
        OptionCategory::create(['key' => self::CATEGORY_MAIN]);
        OptionCategory::find(self::CATEGORY_MAIN)->options()->create(
            ['key' => self::OPTION_STANDARD, 'value' => self::OPTION_VALUE_STANDARD]
        );
        OptionCategory::find(self::CATEGORY_MAIN)->options()->create(
            ['key' => self::OPTION_STANDARD2, 'value' => self::OPTION_VALUE_STANDARD2]
        );
    }

    /**
     * Truncate database
     */
    private function truncate()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables           = DB::select('SHOW TABLES');
        $tablesInDatabase = "Tables_in_" . config('database.connections.mysql.database');
        foreach ($tables as $table) {
            if ($table[$tablesInDatabase] !== 'migrations') {
                DB::table($table[$tablesInDatabase])->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
