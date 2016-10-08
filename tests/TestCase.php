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

        $this->app = new Illuminate\Foundation\Application(
            realpath(__DIR__ . '/app')
        );

        $this->app->singleton(
            'Illuminate\Contracts\Console\Kernel',
            'App\Console\Kernel'
        );

        $this->app->singleton(
            'Illuminate\Contracts\Debug\ExceptionHandler',
            'Illuminate\Foundation\Exceptions\Handler'
        );

        $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $this->app;
    }

    /**
     * Queue up a database disconnect to be performed during a tear down.
     */
    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            DB::disconnect();
        });

        parent::tearDown();
    }
}
