<?php

use Faker\Factory;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Gzero\Entity\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Class DummyValidator
 */
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
        $this->truncate();
        $this->seedLangs();
        $this->seedContentTypes();
        $this->seedUsers();
    }

    /**
     * Seed langs
     *
     * @return array
     */
    private function seedLangs()
    {
        $langs       = [];
        $langs['en'] = Lang::find('en');
        if (empty($langs['en'])) {
            $langs['en'] = new Lang(
                [
                    'code'      => 'en',
                    'i18n'      => 'en_US',
                    'isEnabled' => true,
                    'isDefault' => true
                ]
            );
            $langs['en']->save();
        }

        $langs['pl'] = Lang::find('pl');
        if (empty($langs['pl'])) {
            $langs['pl'] = new Lang(
                [
                    'code'      => 'pl',
                    'i18n'      => 'pl_PL',
                    'isEnabled' => true
                ]
            );
            $langs['pl']->save();
        }
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
     * Seed users
     *
     * @return array
     */
    private function seedUsers()
    {
        // Create user
        $user = User::find(1);
        if (!$user) {
            $user = User::create(
                [
                    'email'     => 'a@a.pl',
                    'firstName' => 'John',
                    'lastName'  => 'Doe',
                    'password'  => Hash::make('test')

                ]
            );
        }
        return [null, $user->id];
    }

    /**
     * Truncate database
     */
    private function truncate()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables             = \DB::select('SHOW TABLES');
        $tables_in_database = "Tables_in_" . \Config::get('database.connections.mysql.database');
        foreach ($tables as $table) {
            if ($table[$tables_in_database] !== 'migrations') {
                \DB::table($table[$tables_in_database])->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
