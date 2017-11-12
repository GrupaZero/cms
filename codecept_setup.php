<?php

namespace App;

use Barryvdh\Cors\ServiceProvider as CORSServiceProvider;
use Dotenv\Dotenv;
use Gzero\Core\Exceptions\Handler;
use Gzero\Core\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Orchestra\Testbench\Traits\CreatesApplication;

require_once __DIR__ . '/tests/fixture/User.php';
require __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env.testing')) {
    (new Dotenv(__DIR__, '.env.testing'))->load();
}

if (!class_exists('App\TestApp')) {
    class TestApp {

        static $oldDb = null;

        use CreatesApplication;

        protected function getPackageProviders($app)
        {
            $routes = $app['router']->getRoutes();

            // The URL generator needs the route collection that exists on the router.
            // Keep in mind this is an object, so we're passing by references here
            // and all the registered routes will be available to the generator.
            $app->instance('routes', $routes);

            // Register Exception handler
            $app->singleton(
                ExceptionHandler::class,
                Handler::class
            );

            // We need to tell Laravel Passport where to find oauth keys
            Passport::loadKeysFrom(__DIR__ . '/vendor/gzero/testing/oauth/');

            return [
                PassportServiceProvider::class,
                CORSServiceProvider::class,
                ServiceProvider::class
            ];
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
            // Reuse existing db to prevent transaction between application calls
            if (static::$oldDb !== null) {
                $app->singleton('db', function () {
                    return static::$oldDb;
                });
            } else {
                if (isset($app['db']) && $app['db']->connection()) {
                    static::$oldDb = $app['db'];
                }
            }

            /** @TODO Why I need to do this here? Are we fine with overriding config options in service providers? */
            // Use passport as guard for api
            $app['config']->set('auth.guards.api.driver', 'passport');
            // We want to return Access-Control-Allow-Credentials header as well
            $app['config']->set('cors.supportsCredentials', true);
        }
    }

}

$app = (new TestApp)->createApplication();


return $app;
