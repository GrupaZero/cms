<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Exception;
use Illuminate\Support\Facades\DB;

class AddContentTranslation {

    /** @var Content */
    protected $content;

    /** @var string */
    protected $languageCode;

    /** @var string */
    protected $title;

    /** @var array */
    protected $attributes;

    /** @var string */
    protected $teaser;

    /** @var string */
    protected $body;

    /** @var string */
    protected $seoTitle;

    /** @var string */
    protected $seoDescription;

    /**
     * Create a new job instance.
     *
     * @param Content $content      Content model
     * @param string  $languageCode Language code
     * @param string  $title        Title
     * @param array   $attributes   Array of optional attributes
     *
     * @internal param array $attributes Array of attributes
     */
    public function __construct(
        Content $content,
        string $languageCode,
        string $title,
        array $attributes = []
    ) {
        $this->content        = $content;
        $this->languageCode   = $languageCode;
        $this->title          = $title;
        $this->teaser         = array_get($attributes, 'teaser', null);
        $this->body           = array_get($attributes, 'body', null);
        $this->seoTitle       = array_get($attributes, 'seo_title', null);
        $this->seoDescription = array_get($attributes, 'seo_description', null);
    }

    /**
     * Execute the job.
     *
     * @return bool
     * @throws Exception
     */
    public function handle()
    {
        $translation = DB::transaction(
            function () {
                $translation = new ContentTranslation();
                $translation->fill([
                    'language_code'   => $this->languageCode,
                    'title'           => $this->title,
                    'teaser'          => $this->teaser,
                    'body'            => $this->body,
                    'seo_title'       => $this->seoTitle,
                    'seo_description' => $this->seoDescription,
                    'is_active'       => true,
                ]);

                $this->content->disableActiveTranslations($translation->language_code);
                $this->content->translations()->save($translation);
                $this->content->createRouteWithUniquePath($translation);

                event('content.translation.created', [$translation]);
                return $translation;
            }
        );
        return $translation;
    }
}
