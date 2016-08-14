<?php namespace Gzero\Core;

use Illuminate\Support\ServiceProvider as SP;
use Illuminate\Foundation\AliasLoader;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class AbstractServiceProvider
 *
 * @package    Gzero
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class AbstractServiceProvider extends SP {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [];

    /**
     * List of service providers aliases
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerAdditionalProviders();
        $this->registerProvidersAliases();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }

    /**
     * Register additional providers to system
     *
     * @return void
     */
    protected function registerAdditionalProviders()
    {
        foreach ($this->providers as $provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * Register additional providers aliases
     *
     * @return void
     */
    protected function registerProvidersAliases()
    {
        $loader = AliasLoader::getInstance();
        foreach ($this->aliases as $alias => $provider) {
            if (class_exists($provider)) {
                $loader->alias(
                    $alias,
                    $provider
                );
            }
        }
    }

    /**
     * Register polices
     *
     * @return void
     */
    protected function registerPolicies()
    {
        $gate = app('Illuminate\Contracts\Auth\Access\Gate');
        $gate->before(
            function ($user) {
                //if ($user->isSuperAdmin()) {
                //    return true;
                //}
            }
        );
        foreach ($this->policies as $key => $value) {
            $gate->policy($key, $value);
        }
    }
}
