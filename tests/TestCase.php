<?php

/**
 * This is simple laravel application test
 */
class TestCase extends Illuminate\Foundation\Testing\TestCase {

    /**
     * @var bool
     */
    static $initialSetup = false;

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $unitTesting = true;

        $testEnvironment = 'testing';

        $env       = 'testing';
        $this->app = $app = new Illuminate\Foundation\Application;


        $app->bindInstallPaths(
            [
                'app'     => __DIR__ . '/app',
                'public'  => __DIR__ . '/public',
                'base'    => __DIR__ . '/',
                'storage' => __DIR__ . '/app/storage',

            ]
        );
        $framework = __DIR__ . '/../vendor/laravel/framework/src';

        require $framework . '/Illuminate/Foundation/start.php';
        require __DIR__ . '/../src/Gzero/Core/helpers.php';

        if (!self::$initialSetup) {
            try {
                $this->app['artisan']->call('migrate:reset');
            } catch (Exception $e) {
            }
            $this->app['artisan']->call('migrate', ['--path' => '../src/migrations']); // Relative to tests/app/
            self::$initialSetup = true;
        }

        return $this->app;
    }
}
