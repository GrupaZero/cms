<?php namespace Cms\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Gzero\Cms\Models\Block;
use Gzero\Cms\Models\BlockTranslation;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\User;
use Illuminate\Routing\Router;

class Functional extends \Codeception\Module {

    /**
     * Create user and return entity
     *
     * @param array $attributes
     *
     * @return User
     */
    public function haveUser($attributes = [])
    {
        return factory(User::class)->create($attributes);
    }

    /**
     * Create users and return collection
     *
     * @param array|integer $users
     *
     * @return array
     */
    public function haveUsers($users)
    {
        if (is_numeric($users)) {
            return factory(User::class, $users)->create();
        }

        $result = [];

        foreach ($users as $attributes) {
            $result[] = $this->haveUser($attributes);
        }

        return $result;
    }

    /**
     * Create content with translations and routes and return entity
     *
     * @param array $attributes
     *
     * @return \Gzero\Cms\Models\Content
     */
    public function haveContent($attributes = [])
    {
        $data            = array_except($attributes, ['translations']);
        $transByLangCode = collect(array_get($attributes, 'translations'))->groupBy('language_code');

        $content = factory(Content::class)->make($data);
        $content->setAsRoot();

        if (empty($transByLangCode)) {
            return $content;
        }

        $transByLangCode->each(function ($translations) use ($content) {
            $firstTranslation = array_first($translations);

            // Create route translation based on the first translations in this language
            $content->routes()
                ->save(
                    factory(Route::class)
                        ->make(
                            [
                                'routable_id'   => $content->id,
                                'routable_type' => Content::class,
                                'language_code' => $firstTranslation['language_code'],
                                'path'          => str_slug($firstTranslation['title']),
                                'is_active'     => array_get($firstTranslation, 'is_active', true)
                            ]
                        )
                );

            // Create content translations
            foreach ($translations as $translation) {
                $content->translations()
                    ->save(
                        factory(ContentTranslation::class)
                            ->make($translation)
                    );
            }
        });

        return $content;
    }


    /**
     * Create content with translations and routes and returns collection
     *
     * @param array $contents
     *
     * @return array
     */
    public function haveContents($contents = [])
    {

        $result = [];

        foreach ($contents as $attributes) {
            $result[] = $this->haveContent($attributes);
        }

        return $result;
    }

    /**
     * @param callable $closure
     */
    public function haveMlRoutes(callable $closure)
    {
        $this->getModule('Laravel5')
            ->haveApplicationHandler(function ($app) use ($closure) {
                addMultiLanguageRoutes(function ($router, $language) use ($closure) {
                    /** @var Router $router */
                    $closure($router, $language);

                    $router->getRoutes()->refreshActionLookups();
                    $router->getRoutes()->refreshNameLookups();
                });
            });
    }

    /**
     * @param callable $closure
     */
    public function haveRoutes(callable $closure)
    {
        $this->getModule('Laravel5')
            ->haveApplicationHandler(function ($app) use ($closure) {
                /** @var Router $router */
                $router = $app->make('router');
                $closure($router);

                $router->getRoutes()->refreshActionLookups();
                $router->getRoutes()->refreshNameLookups();
            });
    }

    /**
     * It clears all application handlers
     */
    public function clearRoutes()
    {
        $this->getModule('Laravel5')->clearApplicationHandlers();
    }

    /**
     * Create block with translations and return entity
     *
     * @param array $attributes
     *
     * @return \Gzero\Cms\Models\Block
     */
    public function haveBlock($attributes = [])
    {
        $data            = array_except($attributes, ['translations']);
        $transByLangCode = collect(array_get($attributes, 'translations'))->groupBy('language_code');

        $block = factory(Block::class)->make($data);
        $block->save();

        if (empty($transByLangCode)) {
            return $block;
        }

        $transByLangCode->each(function ($translations) use ($block) {
            // Create block translations
            foreach ($translations as $translation) {
                $block->translations()
                    ->save(
                        factory(BlockTranslation::class)
                            ->make($translation)
                    );
            }
        });

        return $block;
    }
}
