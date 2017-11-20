<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Cms\Models\ContentType;
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
        $this->theme            = array_get($attributes, 'theme', false);
        $this->weight           = array_get($attributes, 'weight', 0);
        $this->isActive         = array_get($attributes, 'is_active', false);
        $this->isOnHome         = array_get($attributes, 'is_on_home', false);
        $this->isPromoted       = array_get($attributes, 'is_promoted', false);
        $this->isSticky         = array_get($attributes, 'is_sticky', false);
        $this->isCommentAllowed = array_get($attributes, 'is_comment_allowed', false);
        $this->publishedAt      = array_get($attributes, 'published_at', date('Y-m-d H:i:s'));
    }

    /**
     * Execute the job.
     *
     * @return bool
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
                $content->setAsRoot();
                $content->save();

                $translation = new ContentTranslation();
                $translation->fill([
                    'language_code' => $this->languageCode,
                    'title'         => $this->title,
                    'is_active'     => true,
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
