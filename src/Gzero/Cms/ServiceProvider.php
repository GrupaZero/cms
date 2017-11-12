<?php namespace Gzero\Cms;

use Bkwld\Croppa\ServiceProvider as CroppaServiceProvider;
use DaveJamesMiller\Breadcrumbs\Facade as BreadcrumbsFacade;
use DaveJamesMiller\Breadcrumbs\ServiceProvider as BreadcrumbServiceProvider;
use Gzero\Core\AbstractServiceProvider;
use Gzero\Cms\Models\Block;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\File;
use Gzero\Core\Policies\BlockPolicy;
use Gzero\Core\Policies\ContentPolicy;
use Gzero\Core\Policies\FilePolicy;
use Gzero\Repository\LangRepository;

class ServiceProvider extends AbstractServiceProvider {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [
        BreadcrumbServiceProvider::class,
        CroppaServiceProvider::class,
    ];

    /**
     * List of service providers aliases
     *
     * @var array
     */
    protected $aliases = [
        'Breadcrumbs' => BreadcrumbsFacade::class
    ];

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Block::class   => BlockPolicy::class,
        Content::class => ContentPolicy::class,
        File::class    => FilePolicy::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->mergeConfig();
        $this->bindRepositories();
        $this->bindTypes();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerMigrations();
    }

    /**
     * Bind Doctrine 2 repositories
     *
     * @return void
     */
    protected function bindRepositories()
    {
        /*
        $this->app->singleton(

            'gzero.menu.account',
            function () {
                return new Register();
            }
        );
        */

        $this->app->singleton(
            'croppa.src_dir',
            function () {
                return app('filesystem')->disk(config('gzero.upload.disk'))->getDriver();
            }
        );
    }

    /**
     * Bind entities types classes
     *
     * @return void
     */
    protected function bindTypes()
    {
        $entities = [
            'block',
            'content',
            'file'
        ];

        foreach ($entities as $entity) {
            $key = "gzero.$entity" . '_type';
            if (isset($this->app['config'][$key])) {
                foreach ($this->app['config'][$key] as $type => $class) {
                    $this->app->bind("$entity:type:$type", $class);
                }
            }
        }
    }

    /**
     *
     * @TODO How about running this in multiple service providers?
     * Register polices
     *
     * @return void
     */
    protected function registerPolicies()
    {
        $gate = app('Illuminate\Contracts\Auth\Access\Gate');
        $gate->before(
            function ($user) {
                if ($user->isSuperAdmin()) {
                    return true;
                }

                if ($user->isGuest()) {
                    return false;
                }
            }
        );
        foreach ($this->policies as $key => $value) {
            $gate->policy($key, $value);
        }
    }

    /**
     * It registers gzero config
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/config.php',
            'gzero-cms'
        );
    }

    /**
     * It registers db migrations
     *
     * @return void
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
    }

}
