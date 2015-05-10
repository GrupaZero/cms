<?php namespace Gzero\Core;

use Gzero\Core\Menu\Register;
use Gzero\Repository\LangRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider as SP;
use Symfony\Component\HttpFoundation\Request;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ServiceProvider
 *
 * @package    Gzero
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class ServiceProvider extends SP {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHelpers();
        $this->registerFilters();
        $this->bindRepositories();
        $this->bindTypes();
        $this->bindOtherStuff();
        $this->bindCommands();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->detectLanguage();
        $this->registerCommands();
    }

    /**
     * Try to detect language from uri
     *
     * @return void
     */
    protected function detectLanguage()
    {
        if (\Request::segment(1) != 'admin' && $this->app['config']['gzero.multilang.enabled']) {
            if ($this->app['config']['gzero.multilang.subdomain']) {
                $locale = preg_replace('/\..+$/', '', Request::getHost());
            } else {
                $locale = \Request::segment(1);
            }
            $languages = ['pl', 'en'];
            if (in_array($locale, $languages, true)) {
                App::setLocale($locale);
                $this->app['config']['gzero.multilang.detected'] = true;
            }
        }
    }

    /**
     * Bind Doctrine 2 repositories
     *
     * @return void
     */
    protected function bindRepositories()
    {
        $this->app->singleton(
            'user.menu',
            function ($app) {
                return new Register();
            }
        );

        // We need only one LangRepository
        $this->app->singleton(
            'Gzero\Repository\LangRepository',
            function ($app) {
                return new LangRepository(App::make('cache'));
            }
        );
    }

    /**
     * Bind content and block types
     *
     * @return void
     */
    protected function bindTypes()
    {
        foreach ($this->app['config']['gzero.block_type'] as $type => $class) {
            $this->app->bind("block_type:$type", $class);
        }

        foreach ($this->app['config']['gzero.content_type'] as $type => $class) {
            $this->app->bind("content_type:$type", $class);
        }
    }

    /**
     * Bind additional commands
     *
     * @return void
     */
    public function bindCommands()
    {
        //
    }

    /**
     * Register additional commands
     *
     * @return void
     */
    public function registerCommands()
    {
        //
    }

    /**
     * Register additional providers to system
     *
     * @return void
     */
    protected function registerAdditionalProviders()
    {
        foreach ($this->providers as $provider) {
            App::register($provider);
        }
    }

    /**
     * Bind other services
     *
     * @return void
     */
    protected function bindOtherStuff()
    {
        //
    }

    /**
     * Add additional file to store filters
     *
     * @return void
     */
    protected function registerFilters()
    {
        require __DIR__ . '/filters.php';
    }

    /**
     * Add additional file to store helpers
     *
     * @return void
     */
    protected function registerHelpers()
    {
        require_once __DIR__ . '/helpers.php';
    }

}
