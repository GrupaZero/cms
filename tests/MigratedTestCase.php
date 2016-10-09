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
            echo 'Migrate';
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
                'username'  => 'gzero-tests',
                'password'  => 'test',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
                'engine'    => null,
            ]
        );


    }

    protected function getPackageProviders($app)
    {
        $app['config']->set(
            'gzero.file_type',
            [
                'image'    => 'Gzero\Core\Handler\File\Image',
                'document' => 'Gzero\Core\Handler\File\Document',
                'video'    => 'Gzero\Core\Handler\File\Video',
                'music'    => 'Gzero\Core\Handler\File\Music'
            ]
        );
        $app['config']->set(
            'gzero.block_type',
            [
                'basic'   => 'Gzero\Core\Handler\Block\Basic',
                'content' => 'Gzero\Core\Handler\Block\Content',
                'menu'    => 'Gzero\Core\Handler\Block\Menu',
                'slider'  => 'Gzero\Core\Handler\Block\Slider',
                'widget'  => 'Gzero\Core\Handler\Block\Widget'
            ]
        );

        $app['config']->set(
            'gzero.content_type',
            [
                'content'  => 'Gzero\Core\Handler\Content\Content',
                'category' => 'Gzero\Core\Handler\Content\Category'
            ]
        );
        return [Gzero\Core\ServiceProvider::class];
    }
}
