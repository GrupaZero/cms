<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Core\DBTransactionTrait;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Route;

class UpdateContentRoute {

    use DBTransactionTrait;

    /** @var Content */
    protected $content;

    /** @var Language */
    protected $language;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'path',
        'is_active'
    ];

    /**
     * Create a new job instance.
     *
     * @param Content  $content    Content model
     * @param Language $language   Language code
     * @param array    $attributes Array of optional attributes
     */
    public function __construct(Content $content, Language $language, array $attributes = [])
    {
        $this->content    = $content;
        $this->language   = $language;
        $this->attributes = array_only($attributes, $this->allowedAttributes);
    }

    /**
     * Execute the job.
     *
     * @throws \Exception|\Throwable
     *
     * @return Content
     */
    public function handle()
    {
        $content = $this->dbTransaction(function () {
            $route = $this->content->routes()->where('language_code', $this->language->code)->first();

            if ($route->path !== $this->attributes['path']) {
                $this->attributes['path'] = Route::buildUniquePath($this->attributes['path'], $this->language->code);
            }

            $route->fill($this->attributes);
            $route->save();

            event('content.route.updated', [$this->content]);
            return $this->content;
        });
        return $content;
    }
}
