<?php

use Aedart\Testing\Laravel\Traits\TestHelperTrait;

if (file_exists(dirname(__DIR__) . '/.env.testing')) {
    (new \Dotenv\Dotenv(dirname(__DIR__), '.env.testing'))->load();
}

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
        $app['config']->set(
            'database.connections.mysql.modes',
            [
                'ONLY_FULL_GROUP_BY',
                'STRICT_TRANS_TABLES',
                'NO_ZERO_IN_DATE',
                'NO_ZERO_DATE',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_AUTO_CREATE_USER',
                'NO_ENGINE_SUBSTITUTION'
            ]
        );

        $this->beforeApplicationDestroyed(
            function () {
                \DB::disconnect('mysql');
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [Gzero\Core\ServiceProvider::class];
    }

}
