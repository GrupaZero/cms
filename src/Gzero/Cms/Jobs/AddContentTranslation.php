<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Support\Facades\DB;

class AddContentTranslation {

    /** @var Content */
    protected $content;

    /** @var string */
    protected $language;

    /** @var string */
    protected $title;

    /** @var User */
    protected $author;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'teaser'          => null,
        'body'            => null,
        'seo_title'       => null,
        'seo_description' => null,
        'is_active'       => true,
    ];

    /**
     * Create a new job instance.
     *
     * @param Content  $content    Content model
     * @param string   $title      Title
     * @param Language $language   Language code
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @internal param array $attributes Array of attributes
     */
    public function __construct(Content $content, string $title, Language $language, User $author, array $attributes = [])
    {
        $this->content    = $content;
        $this->language   = $language;
        $this->title      = $title;
        $this->author     = $author;
        $this->attributes = array_merge(
            $this->allowedAttributes,
            array_only($attributes, array_keys($this->allowedAttributes))
        );
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle()
    {
        $translation = DB::transaction(
            function () {
                $translation = new ContentTranslation();
                $translation->fill([
                    'title'           => $this->title,
                    'language_code'   => $this->language->code,
                    'teaser'          => $this->attributes['teaser'],
                    'body'            => $this->attributes['body'],
                    'seo_title'       => $this->attributes['seo_title'],
                    'seo_description' => $this->attributes['seo_description'],
                    'is_active'       => true,
                ]);
                $translation->author()->associate($this->author);

                $this->content->disableActiveTranslations($translation->language_code);
                $this->content->translations()->save($translation);
                $this->content->createRoute($translation, $this->attributes['is_active']);

                event('content.translation.created', [$translation]);
                return $translation;
            }
        );
        return $translation;
    }
}
