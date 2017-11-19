<?php namespace Gzero\Cms\Jobs;

use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
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

    /** @var User */
    protected $author;

    /**
     * Create a new job instance.
     *
     * @param string $type             Type
     * @param string $languageCode     Language code
     * @param string $title            Translations title
     * @param User   $author           User model
     * @param bool   $isActive         Active flag
     * @param bool   $isOnHome         On home page flag
     * @param bool   $isPromoted       Promoted flag
     * @param bool   $isSticky         Sticky flag
     * @param bool   $isCommentAllowed Allowing for comments flag
     * @param int    $weight           Weight number
     * @param string $theme            Css classes
     * @param string $publishedAt      Published date
     */
    public function __construct(
        string $type,
        string $languageCode,
        string $title,
        User $author = null,
        ?bool $isActive = null,
        ?bool $isOnHome = null,
        ?bool $isPromoted = null,
        ?bool $isSticky = null,
        ?bool $isCommentAllowed = null,
        ?int $weight = null,
        ?string $theme = null,
        ?string $publishedAt = null
    ) {
        $this->type             = $type;
        $this->languageCode     = $languageCode;
        $this->title            = $title;
        $this->author           = $author;
        $this->isActive         = $isActive;
        $this->isOnHome         = $isOnHome;
        $this->isPromoted       = $isPromoted;
        $this->isSticky         = $isSticky;
        $this->isCommentAllowed = $isCommentAllowed;
        $this->weight           = $weight;
        $this->theme            = $theme;
        $this->publishedAt      = $publishedAt;
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
                    'theme'              => $this->theme ?: null,
                    'weight'             => $this->weight ?: 0,
                    'is_active'          => $this->isActive ?: false,
                    'is_on_home'         => $this->isOnHome ?: false,
                    'is_promoted'        => $this->isPromoted ?: false,
                    'is_sticky'          => $this->isSticky ?: false,
                    'is_comment_allowed' => $this->isCommentAllowed ?: false,
                    'published_at'       => $this->publishedAt ?: date('Y-m-d H:i:s')
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
