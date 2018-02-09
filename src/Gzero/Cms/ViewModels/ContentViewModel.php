<?php namespace Gzero\Cms\ViewModels;

use Carbon\Carbon;
use Gzero\Core\ViewModels\UserViewModel;

class ContentViewModel {

    /** @var array */
    protected $data;

    /** @var array */
    protected $author;

    /** @var array */
    protected $route;

    /** @var array */
    protected $routes;

    /** @var array */
    protected $translation;

    /** @var array */
    protected $translations;

    /** @var array */
    protected $allowedAttributes = [
        'id',
        'theme',
        'weight',
        'level',
        'rating',
        'is_on_home',
        'is_promoted',
        'is_sticky',
        'is_comment_allowed',
        'published_at',
        'updated_at',
        'thumb_id'
    ];

    /**
     * ContentViewModel constructor.
     *
     * @param array $data data to create presenter instance
     */
    public function __construct(array $data)
    {
        $this->data         = array_only($data, $this->allowedAttributes);
        $this->routes       = array_get($data, 'routes', []);
        $this->translations = array_get($data, 'translations', []);
        $this->author       = new UserViewModel(array_get($data, 'author', []));

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
     * @return bool
     */
    public function isOnHome()
    {
        return array_get($this->data, 'is_on_home', false);
    }

    /**
     * @return bool
     */
    public function isPromoted()
    {
        return array_get($this->data, 'is_promoted', false);
    }

    /**
     * @return mixed
     */
    public function isSticky()
    {
        return array_get($this->data, 'is_sticky', false);
    }

    /**
     * @return mixed
     */
    public function isCommentAllowed()
    {
        return array_get($this->data, 'is_comment_allowed', false);
    }

    /**
     * @return mixed
     */
    public function isPublished()
    {
        $publishedAt = array_get($this->data, 'published_at', null);
        if ($publishedAt === null) {
            return false;
        }

        return Carbon::parse($publishedAt)->lte(Carbon::now());
    }

    /**
     * @return mixed
     */
    public function hasTeaser()
    {
        return !empty($this->teaser());
    }

    /**
     * @return mixed
     */
    public function hasThumbnail()
    {
        return $this->data['thumb_id'] !== null;
    }

    /**
     * @return mixed
     */
    public function hasAncestors()
    {
        return (array_get($this->data, 'level', 0) > 0);
    }

    /**
     * @return integer
     */
    public function id()
    {
        return array_get($this->data, 'id');
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function title(string $language = null): ?string
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
    public function teaser(string $language = null): ?string
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
    public function body(string $language = null): ?string
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
    public function url(string $language = null): ?string
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
    public function seoTitle($alternativeField = false)
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
    public function seoDescription($alternativeField = false)
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
    public function theme()
    {
        return array_get($this->data, 'theme');
    }

    /**
     * This function returns formatted publish date
     *
     * @return string
     */
    public function publishedAt()
    {
        $publishedAt = array_get($this->data, 'published_at', null);

        if ($publishedAt === null) {
            return trans('gzero-core::common.unknown');
        }

        return $publishedAt;
    }

    /**
     * This function returns formatted updated date
     *
     * @return string
     */
    public function updatedAt()
    {
        $updatedAt = array_get($this->data, 'updated_at', null);

        if ($updatedAt === null) {
            return trans('gzero-core::common.unknown');
        }

        return $updatedAt;
    }

    /**
     * This function returns author first and last name
     *
     * @return UserViewModel
     */
    public function author()
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
    public function firstImageUrl($text, $default = null)
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
    public function ancestorsNames()
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
