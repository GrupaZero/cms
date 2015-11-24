<?php namespace Gzero\Core;

use Gzero\Core\Menu\Register;
use Gzero\Core\OptionsService;
use Gzero\Repository\LangRepository;
use Gzero\Repository\OptionRepository;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Foundation\AliasLoader;

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
class ServiceProvider extends AbstractServiceProvider {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [
        'Robbo\Presenter\PresenterServiceProvider',
        'DaveJamesMiller\Breadcrumbs\ServiceProvider'
    ];

    /**
     * List of service providers aliases
     *
     * @var array
     */
    protected $aliases = [
        'Breadcrumbs' => 'DaveJamesMiller\Breadcrumbs\Facade',
        'options' => 'Gzero\Core\OptionsService'
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
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
     * We need to do that as soon as possible, because we need to know what language need to be set for ML routes
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
                app()->setLocale($locale);
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
                return new LangRepository(app()->make('cache'));
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
     * Bind other services
     *
     * @return void
     */
    protected function bindOtherStuff()
    {
        app()->singleton(
            'Gzero\Core\OptionsService',
            function (OptionRepository $repo) {
                return new OptionsService($repo);
            }
        );
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
