<?php namespace Gzero\Cms\Presenters;

use Carbon\Carbon;
use Gzero\Core\Presenters\UserPresenter;
use Robbo\Presenter\Presenter;

class ContentPresenter extends Presenter {

    protected $level;

    protected $author;

    protected $route;

    protected $routes;

    protected $translation;

    protected $translations;

    /** @var array */
    protected $allowedAttributes = [
        'id',
        'theme',
        'weight',
        'rating',
        'is_on_home',
        'is_promoted',
        'is_sticky',
        'is_comment_allowed',
        'published_at',
        'updated_at'
    ];

    /**
     * ContentPresenter constructor.
     *
     * @param array $data data to create presenter instance
     */
    public function __construct(array $data)
    {
        $this->object       = array_only($data, $this->allowedAttributes);
        $this->routes       = array_get($data, 'routes', []);
        $this->translations = array_get($data, 'translations', []);
        $this->author       = new UserPresenter(array_get($data, 'author', []));
        $this->level        = array_get($data, 'level');

        $this->translation = array_first($this->translations, function ($translation) {
            return $translation['language_code'] === app()->getLocale();
        }, [
            'title'           => null,
            'teaser'          => null,
            'body'            => null,
            'seo_title'       => null,
            'seo_description' => null
        ]);

        $this->route = array_first($this->routes, function ($route) {
            return $route['language_code'] === app()->getLocale() && $route['is_active'] === true;
        });
    }

    /**
     * @return mixed
     */
    public function isOnHome()
    {
        return $this->is_on_home;
    }

    /**
     * @return mixed
     */
    public function isPromoted()
    {
        return $this->is_promoted;
    }

    /**
     * @return mixed
     */
    public function isSticky()
    {
        return $this->is_sticky;
    }

    /**
     * @return mixed
     */
    public function isCommentAllowed()
    {
        return $this->is_comment_allowed;
    }

    /**
     * @return mixed
     */
    public function isPublished()
    {
        if ($this->published_at === null) {
            return false;
        }

        return Carbon::parse($this->published_at)->lte(Carbon::now());
    }

    /**
     * @return mixed
     */
    public function hasTeaser()
    {
        return !empty($this->getTeaser());
    }

    /**
     * @return mixed
     */
    public function hasThumbnail()
    {
        return !empty($this->thumb_id);
    }

    /**
     * @return mixed
     */
    public function hasAncestors()
    {
        return ($this->level > 0);
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function getTitle(string $language = null): ?string
    {
        if ($language === null) {
            return array_get($this->translation, 'title');
        }

        $translation = array_first($this->translations, function ($translation) use ($language) {
            return $translation['language_code'] === $language;
        });

        return array_get($translation, 'title');
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function getTeaser(string $language = null): ?string
    {
        if ($language === null) {
            return array_get($this->translation, 'teaser');
        }

        $translation = array_first($this->translations, function ($translation) use ($language) {
            return $translation['language_code'] === $language;
        });

        return array_get($translation, 'teaser');
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function getBody(string $language = null): ?string
    {
        if ($language === null) {
            return array_get($this->translation, 'body');
        }

        $translation = array_first($this->translations, function ($translation) use ($language) {
            return $translation['language_code'] === $language;
        });

        return array_get($translation, 'body');
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function getUrl(string $language = null): ?string
    {
        if ($this->route === null) {
            return null;
        }

        if ($language === null) {
            return urlMl(array_get($this->route, 'path'), app()->getLocale());
        }

        $route = array_first($this->routes, function ($route) use ($language) {
            return $route['language_code'] === $language && $route['is_active'] === true;
        });

        return urlMl(array_get($route, 'path'), $language);
    }

    /**
     * Return seoTitle of translation if exists
     * otherwise return generated one
     *
     * @param mixed $alternativeField alternative field to display when seoTitle field is empty
     *
     * @return string
     */
    public function getSeoTitle($alternativeField = false)
    {
        if (!$alternativeField) {
            $alternativeField = config('gzero.seo.alternative_title', 'title');
        }

        $text = $this->removeNewLinesAndWhitespace($this->translation[$alternativeField]);
        // if alternative field is not empty
        if ($text) {
            return $this->translation['seo_title'] ? $this->removeNewLinesAndWhitespace($this->translation['seo_title']) : $text;
        }
        // show site name as default
        return option('general', 'site_name');
    }

    /**
     * Return seoDescription of translation if exists
     * otherwise return generated one
     *
     * @param mixed $alternativeField alternative field to display when seoDescription field is empty
     *
     * @return string
     */
    public function getSeoDescription($alternativeField = false)
    {
        $descLength = option('seo', 'desc_length', config('gzero.seo.desc_length', 160));
        if (!$alternativeField) {
            $alternativeField = config('gzero.seo.alternative_desc', 'body');
        }
        // if SEO description is set
        if ($this->translation['seo_description']) {
            return $this->removeNewLinesAndWhitespace($this->translation['seo_description']);
        }

        $text = $this->removeNewLinesAndWhitespace($this->translation[$alternativeField]);
        // if alternative field is not empty
        if ($text) {
            return strlen($text) >= $descLength ? substr($text, 0, strpos($text, ' ', $descLength)) : $text;
        };
        // show site description as default
        return option('general', 'site_desc');
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return array_get($this, 'theme');
    }

    /**
     * This function returns formatted publish date
     *
     * @return string
     */
    public function getPublishDate()
    {
        if (empty($this->published_at)) {
            return trans('gzero-core::common.unknown');
        }

        return $this->published_at;
    }

    /**
     * This function returns formatted updated date
     *
     * @return string
     */
    public function getUpdatedDate()
    {
        if (empty($this->updated_at)) {
            return trans('gzero-core::common.unknown');
        }

        return $this->updated_at;
    }

    /**
     * This function returns author first and last name
     *
     * @return UserPresenter
     */
    public function getAuthor()
    {
        return optional($this->author);
    }

    /**
     * This function returns the first img url from provided text
     *
     * @param string $text    text to get first image url from
     *
     * @param null   $default default url to return if image is not found
     *
     * @return string first image url
     */
    public function getFirstImageUrl($text, $default = null)
    {
        $url = $default;

        if (!empty($text)) {
            preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $text, $matches);
        }

        if (!empty($matches) && isset($matches[1])) {
            $url = $matches[1];
        }

        return $url;
    }

    /**
     * This function returns names of all ancestors based on route path
     *
     * @return array ancestors names
     */
    public function getAncestorsNames()
    {
        $ancestors = explode('/', array_get($this->route, 'path'));

        if (empty($ancestors)) {
            return null;
        }

        array_pop($ancestors);

        return array_map('ucfirst', $ancestors);
    }

    /**
     * Function removes new lines and strip whitespace (or other characters) from the beginning and end of a string
     *
     * @param string $string  string to replace
     * @param string $replace replacement
     *
     * @return string
     */
    private function removeNewLinesAndWhitespace($string, $replace = " ")
    {
        return str_replace(["\n\r", "\n\n", "\n", "\r"], $replace, trim(strip_tags($string)));
    }
}
