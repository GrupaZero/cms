<?php

use Faker\Factory;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Gzero\Entity\User;
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
     * Seed users
     *
     * @return array
     */
    private function seedUsers()
    {
        // Create users
        $user = User::firstOrCreate(
            [
                'email'     => 'a@a.pl',
                'firstName' => 'John',
                'lastName'  => 'Doe',
                'password'  => Hash::make('test')

            ]
        );

        for ($x = 0; $x < 100; $x++) {
            $str  = md5($x);
            $user = User::firstOrCreate(
                [
                    'email'     => str_replace(range(0, 9), '', substr($str, 0, 8)) . $x . '@' . substr($str, 4, 8) . '.com',
                    'firstName' => str_replace(range(0, 9), '', substr($str, 0, 10)),
                    'lastName'  => str_replace(range(0, 9), '', substr($str, 5, 10)),
                    'password'  => substr($str, 3, 8)
                ]
            );
        }


        return $user;
    }

    /**
     * Truncate database
     */
    private function truncate()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables             = DB::select('SHOW TABLES');
        $tables_in_database = "Tables_in_" . Config::get('database.connections.mysql.database');
        foreach ($tables as $table) {
            if ($table[$tables_in_database] !== 'migrations') {
                DB::table($table[$tables_in_database])->truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
