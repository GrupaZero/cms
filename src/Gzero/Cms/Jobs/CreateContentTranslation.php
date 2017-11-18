<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Exception;
use Gzero\Core\Models\Route;
use Gzero\Core\Repositories\RouteReadRepository;
use Illuminate\Support\Facades\DB;

class CreateContentTranslation {

    /** @var Content */
    protected $content;

    /** @var array */
    protected $attributes;

    /**
     * Create a new job instance.
     *
     * @param Content $content    Content model
     * @param array   $attributes Array of attributes
     *
     */
    public function __construct(Content $content, array $attributes = [])
    {
        $this->content    = $content;
        $this->attributes = array_only($attributes, ['language_code', 'title', 'teaser', 'body', 'seo_title', 'seo_description']);
    }

    /**
     * Execute the job.
     *
     * @return bool
     * @throws Exception
     */
    public function handle()
    {
        // @TODO Job Exception?
        if (!array_key_exists('language_code', $this->attributes) || !array_key_exists('title', $this->attributes)) {
            throw new Exception('Language code and title of translation are required');
        }

        $translation = DB::transaction(
            function () {
                $translation = new ContentTranslation();
                $translation->fill($this->attributes);
                $translation->is_active = true;

                $route                  = $this->content->route()->first() ?: new Route();
                $routeTranslation       = $route->translations()->firstOrNew(
                    [
                        'route_id'      => $route->id,
                        'language_code' => $translation->language_code,
                        'is_active'     => true
                    ]
                );
                $routeTranslation->path = $this->getUniquePath($translation->title, $translation->language_code);

                $this->disableActiveTranslations($translation->language_code);
                $this->content->translations()->save($translation);

                $this->content->route()->save($route);
                $route->translations()->save($routeTranslation);

                return $translation;
            }
        );
        event('content.translation.created', [$translation]);
        return $translation;
    }

    /**
     * Function sets all content translations in provided language code as inactive
     *
     * @param string $languageCode language code
     *
     * @return mixed
     */
    protected function disableActiveTranslations($languageCode)
    {
        return $this->content->translations()
            ->where('content_id', $this->content->id)
            ->where('language_code', $languageCode)
            ->update(['is_active' => false]);
    }

    /**
     * Function returns an unique path address from given path in specific language
     *
     * @param string $path         string path to search for
     * @param string $languageCode translation language code
     *
     * @return string an unique path address
     */
    protected function getUniquePath($path, $languageCode)
    {
        // @TODO use parent path

        return (new RouteReadRepository())->buildUniquePath(str_slug($path), $languageCode);
    }
}
