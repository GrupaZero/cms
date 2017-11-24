<?php namespace Cms\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\RouteTranslation;
use Gzero\Core\Models\User;

class Unit extends \Codeception\Module {

    /**
     * Create user and return entity
     *
     * @param array $attributes
     *
     * @return \Gzero\Core\Models\User
     */
    public function haveUser($attributes = [])
    {
        return factory(User::class)->create($attributes);
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

        $route = $content->route()
            ->save(
                factory(Route::class)
                    ->make(
                        [
                            'routable_id'   => $content->id,
                            'routable_type' => Content::class
                        ]
                    )
            );

        $transByLangCode->each(function ($translations) use ($content, $route) {
            $firstTranslation = array_first($translations);

            // Create route translation based on the first translations in this language
            $route->translations()
                ->save(
                    factory(RouteTranslation::class)
                        ->make([
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
}
