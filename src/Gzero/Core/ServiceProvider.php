<?php namespace Gzero\Core;

use Illuminate\Support\Facades\Config;
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
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerHelpers();
        $this->registerFilters();
        $this->detectLanguage();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->app['config']['gzero.content_type'] as $type => $class) {
            $this->app->bind("content_type:$type", $class);
        }
    }

    /**
     * Trying to detect language from uri
     */
    protected function detectLanguage()
    {
        if (\Request::segment(1) != 'admin' and $this->app['config']['gzero.multilang.enabled']) {
            if ($this->app['config']['gzero.multilang.subdomain']) {
                $locale = preg_replace('/\..+$/', '', Request::getHost());
            } else {
                $locale = \Request::segment(1);
            }
            $languages = array('pl', 'en');
            if (in_array($locale, $languages, TRUE)) {
                App::setLocale($locale);
                $this->app['config']['gzero.multilang.detected'] = TRUE;
            }
        }
    }

    /**
     * Register additional providers to system
     */
    protected function registerAdditionalProviders()
    {
        foreach ($this->providers as $provider) {
            App::register($provider);
        }
    }

    /**
     * Add additional file to store filers
     */
    protected function registerFilters()
    {
        require __DIR__ . '/filters.php';
    }

    /**
     * Add additional file to store helpers
     */
    protected function registerHelpers()
    {
        require_once __DIR__ . '/helpers.php';
    }
}
