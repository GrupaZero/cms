<?php

use Aedart\Testing\Laravel\Traits\TestHelperTrait;

/**
 * This is simple laravel application test
 */
class TestCase extends \Codeception\Test\Unit {

    use TestHelperTrait;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
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
                'modes' => [
                    'ONLY_FULL_GROUP_BY',
                    'STRICT_TRANS_TABLES',
                    'NO_ZERO_IN_DATE',
                    'NO_ZERO_DATE',
                    'ERROR_FOR_DIVISION_BY_ZERO',
                    'NO_AUTO_CREATE_USER',
                    'NO_ENGINE_SUBSTITUTION'
                ],
                'strict'    => true, // Not used when modes specified
                'engine'    => null,
            ]
        );

        $this->beforeApplicationDestroyed(
            function () {
                \DB::disconnect('testbench');
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [Gzero\Core\ServiceProvider::class];
    }

}
