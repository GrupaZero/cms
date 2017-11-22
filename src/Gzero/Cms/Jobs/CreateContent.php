<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Exception;
use Gzero\Core\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateContent {

    /** @var string */
    protected $type;

    /** @var string */
    protected $languageCode;

    /** @var string */
    protected $title;

    /** @var array */
    protected $attributes;

    /** @var User */
    protected $author;

    /** @var int */
    protected $parentId;

    /** @var string */
    protected $theme;

    /** @var int */
    protected $weight;

    /** @var bool */
    protected $isActive;

    /** @var bool */
    protected $isOnHome;

    /** @var bool */
    protected $isPromoted;

    /** @var bool */
    protected $isSticky;

    /** @var bool */
    protected $isCommentAllowed;

    /** @var string */
    protected $publishedAt;

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
     * @param string $type         Type
     * @param string $languageCode Language code
     * @param string $title        Translations title
     * @param array  $attributes   Array of optional attributes
     * @param User   $author       User model
     */
    public function __construct(
        string $type,
        string $languageCode,
        string $title,
        array $attributes = [],
        User $author = null
    ) {
        $this->type             = $type;
        $this->languageCode     = $languageCode;
        $this->title            = $title;
        $this->author           = $author;
        $this->theme            = array_get($attributes, 'theme', null);
        $this->parentId         = array_get($attributes, 'parent_id', null);
        $this->weight           = array_get($attributes, 'weight', 0);
        $this->isActive         = array_get($attributes, 'is_active', false);
        $this->isOnHome         = array_get($attributes, 'is_on_home', false);
        $this->isPromoted       = array_get($attributes, 'is_promoted', false);
        $this->isSticky         = array_get($attributes, 'is_sticky', false);
        $this->isCommentAllowed = array_get($attributes, 'is_comment_allowed', false);
        $this->publishedAt      = array_get($attributes, 'published_at', date('Y-m-d H:i:s'));
        $this->teaser           = array_get($attributes, 'teaser', null);
        $this->body             = array_get($attributes, 'body', null);
        $this->seoTitle         = array_get($attributes, 'seo_title', null);
        $this->seoDescription   = array_get($attributes, 'seo_description', null);
    }

    /**
     * Execute the job.
     *
     * @return bool
     * @throws Exception
     */
    public function handle()
    {
        $content = DB::transaction(
            function () {
                $author  = $this->author ?: Auth::user();
                $content = new Content();
                $content->fill([
                    'type'               => $this->type,
                    'theme'              => $this->theme,
                    'weight'             => $this->weight,
                    'is_active'          => $this->isActive,
                    'is_on_home'         => $this->isOnHome,
                    'is_promoted'        => $this->isPromoted,
                    'is_sticky'          => $this->isSticky,
                    'is_comment_allowed' => $this->isCommentAllowed,
                    'published_at'       => $this->publishedAt
                ]);
                $content->author()->associate($author);

                if ($this->parentId) {
                    $parent = (new ContentReadRepository())->getById($this->parentId);

                    if ($parent->type !== 'category') {
                        throw new Exception("Content type '$parent->type' is not allowed for the parent type.");
                    }

                    $content->setChildOf($parent);
                } else {
                    $content->setAsRoot();
                }

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

                $content->disableActiveTranslations($translation->language_code);
                $content->translations()->save($translation);
                $content->createRouteWithUniquePath($translation);

                event('content.created', [$content]);
                return $content;
            }
        );
        return $content;
    }

}
