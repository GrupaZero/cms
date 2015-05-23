<?php require 'app/Console/Kernel.php';

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

        if (!self::$initialSetup) {
            try {
                $this->artisan('migrate:reset');
            } catch (Exception $e) {
            }
            $this->artisan('migrate', ['--path' => '/../../src/migrations']);  // Relative to tests/app/
            self::$initialSetup = true;
        }

        return $this->app;
    }
}
