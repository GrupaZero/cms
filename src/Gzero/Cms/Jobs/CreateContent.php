<?php namespace Gzero\Cms\Jobs;

use Carbon\Carbon;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Exception;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Support\Facades\DB;

class CreateContent {

    /** @var string */
    protected $title;

    /** @var string */
    protected $language;

    /** @var User */
    protected $author;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $allowedAttributes = [
        'type'               => 'content',
        'theme'              => null,
        'parent_id'          => null,
        'weight'             => 0,
        'is_active'          => false,
        'is_on_home'         => false,
        'is_promoted'        => false,
        'is_sticky'          => false,
        'is_comment_allowed' => false,
        'published_at'       => null,
        'teaser'             => null,
        'body'               => null,
        'seo_title'          => null,
        'seo_description'    => null
    ];

    /**
     * Create a new job instance.
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     */
    protected function __construct(string $title, Language $language, User $author, array $attributes = [])
    {
        $this->title      = $title;
        $this->language   = $language;
        $this->author     = $author;
        $this->attributes = array_merge(
            $this->allowedAttributes,
            array_only($attributes, array_keys($this->allowedAttributes))
        );
    }

    /**
     * It creates job to create content
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @return CreateContent
     */
    public static function make(string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($title, $language, $author, $attributes);
    }

    /**
     * It creates job to create content
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @return CreateContent
     */
    public static function content(string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($title, $language, $author, array_merge($attributes, ['type' => 'content']));
    }

    /**
     * It creates job to create content category
     *
     * @param string   $title      Translation title
     * @param Language $language   Language
     * @param User     $author     User model
     * @param array    $attributes Array of optional attributes
     *
     * @return CreateContent
     */
    public static function category(string $title, Language $language, User $author, array $attributes = [])
    {
        return new self($title, $language, $author, array_merge($attributes, ['type' => 'category']));
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     *
     * @return Content
     */
    public function handle()
    {
        $content = DB::transaction(
            function () {
                $content = new Content();
                $content->fill([
                    'type'               => $this->attributes['type'],
                    'theme'              => $this->attributes['theme'],
                    'weight'             => $this->attributes['weight'],
                    'is_on_home'         => $this->attributes['is_on_home'],
                    'is_promoted'        => $this->attributes['is_promoted'],
                    'is_sticky'          => $this->attributes['is_sticky'],
                    'is_comment_allowed' => $this->attributes['is_comment_allowed'],
                    'published_at'       => $this->attributes['published_at']
                ]);
                $content->author()->associate($this->author);

                if (!$this->attributes['published_at']) {
                    $content->published_at = Carbon::now();
                }

                if ($this->attributes['parent_id']) {
                    $parent = Content::find($this->attributes['parent_id']);
                    if (!$parent) {
                        throw new Exception('Parent not found');
                    }

                    $content->setChildOf($parent);
                } else {
                    $content->setAsRoot();
                }

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

                $content->disableActiveTranslations($translation->language_code);
                $content->translations()->save($translation);
                $content->createRoute($translation, $this->attributes['is_active']);

                event('content.created', [$content]);
                return $content;
            }
        );
        return $content;
    }

}
