<?php

/**
 * This is simple laravel application test
 */
class TestCase extends Illuminate\Foundation\Testing\TestCase {

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

        return $this->app;
    }

}
