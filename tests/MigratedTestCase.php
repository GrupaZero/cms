<?php

/**
 * This is simple laravel application test
 */
class MigratedTestCase extends Orchestra\Testbench\TestCase {

    /**
     * @var bool
     */
    static $initialSetup = false;

    protected function setUp()
    {
        parent::setUp();
        if (!self::$initialSetup) { // We only want to migrate once at the beginning of tests
            self::$initialSetup = true;
            $this->artisan(
                'migrate',
                [
                    '--database' => 'testbench',
                    '--realpath' => realpath(__DIR__ . '/../database/migrations'),
                ]
            );
        }
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench',
            [
                'driver'    => 'mysql',
                'host'      => 'localhost',
                'port'      => 3306,
                'database'  => 'gzero-tests',
                'username'  => 'root',
                'password'  => '',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
            ]
        );


    }

    protected function getPackageProviders($app)
    {
        return [Gzero\Core\ServiceProvider::class];
    }
}
