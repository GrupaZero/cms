<?php namespace Gzero\Cms;

use Bkwld\Croppa\ServiceProvider as CroppaServiceProvider;
use Gzero\Cms\Listeners\BlockCacheClear;
use Gzero\Cms\Listeners\BlockLoad;
use Gzero\Cms\Policies\BlockPolicy;
use Gzero\Cms\Policies\ContentPolicy;
use Gzero\Core\AbstractServiceProvider;
use Gzero\Core\Events\RouteMatched as GzeroRouteMatched;
use Illuminate\Database\Eloquent\Factory;
use Gzero\Cms\Models\Block;
use Gzero\Cms\Models\Content;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;

class ServiceProvider extends AbstractServiceProvider {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [
        CroppaServiceProvider::class,
    ];

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
    protected $policies = [
        Block::class   => BlockPolicy::class,
        Content::class => ContentPolicy::class
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
        $this->registerHelpers();
        $this->bindRepositories();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerPolicies();
        $this->registerMigrations();
        $this->registerFactories();
        $this->registerViews();
        $this->registerViewComposers();
        $this->registerListeners();
        $this->registerPublishes();
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
     * Add additional file to store helpers
     *
     * @return void
     */
    protected function registerHelpers()
    {
        require __DIR__ . '/helpers.php';
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
     * It registers factories
     *
     * @return void
     */
    protected function registerFactories()
    {
        resolve(Factory::class)->load(__DIR__ . '/../../../database/factories');
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

    /**
     * It register all views
     *
     * @return void
     */
    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../../../resources/views', 'gzero-cms');
    }

    /**
     * It registers view composers
     *
     * @return void
     */
    protected function registerViewComposers()
    {
        view()->composer(
            'gzero-cms::contents._disqus',
            function ($view) {
                $data = [];
                $user = auth()->user();
                if ($user && !$user->isGuest()) {
                    $data = [
                        "id"       => $user["id"],
                        "username" => $user->getPresenter()->displayName(),
                        "email"    => $user["email"],
                        //"avatar"    => $user["avatar"],
                    ];
                }

                $message   = base64_encode(json_encode($data));
                $timestamp = time();
                $hmac      = $this->dsqHmacSha1($message . ' ' . $timestamp, config('gzero-cms.disqus.api_secret'));
                $view->with('remoteAuthS3', "$message $hmac $timestamp");
            }
        );
    }

    /**
     * It generates HMAC hash value. It was originally created as inner function in view gzero-cms::contents._disqus
     * composer method callback, but phpcs complains about this.
     *
     * @param string $data data to hash
     * @param string $key  secret key
     *
     * @return string
     */
    private function dsqHmacSha1(string $data, string $key)
    {
        $blockSize = 64;
        if (strlen($key) > $blockSize) {
            $key = pack('H*', sha1($key));
        }
        $key  = str_pad($key, $blockSize, chr(0x00));
        $ipad = str_repeat(chr(0x36), $blockSize);
        $opad = str_repeat(chr(0x5c), $blockSize);
        $hmac = pack(
            'H*',
            sha1(
                ($key ^ $opad) . pack(
                    'H*',
                    sha1(
                        ($key ^ $ipad) . $data
                    )
                )
            )
        );
        return bin2hex($hmac);
    }

    /**
     * Add additional file to store routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/api.php');
    }

    /**
     * It registers all assets to publish
     *
     * @return void
     */
    protected function registerPublishes()
    {
        // Views
        $this->publishes(
            [
                __DIR__ . '/../../../resources/views' => resource_path('views/vendor/gzero-cms'),
            ],
            'gzero-cms views'
        );
    }

    /**
     * It registers event listeners
     *
     * @return void
     */
    protected function registerListeners()
    {
        Event::listen(RouteMatched::class, BlockLoad::class);
        Event::listen(GzeroRouteMatched::class, BlockLoad::class);
        Event::listen('block.*', BlockCacheClear::class);
    }

}
