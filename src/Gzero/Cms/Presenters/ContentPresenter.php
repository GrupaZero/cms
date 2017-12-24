<?php namespace Gzero\Cms\Presenters;

use Robbo\Presenter\Presenter;

class ContentPresenter extends Presenter {

    protected $translation;

    protected $translations;

    /** @var array */
    protected $allowedAttributes = [
        'theme',
        'weight',
        'rating',
        'is_on_home',
        'is_promoted',
        'is_sticky',
        'is_comment_allowed',
        'published_at'
    ];

    /**
     * ContentPresenter constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->object       = array_only($data, $this->allowedAttributes);
        $this->translations = array_get($data, 'translations', []);
        $this->translation  = array_first($this->translations, function ($translation) {
            return $translation['language_code'] === app()->getLocale();
        });
    }

    /**
     * @param string|null $language
     *
     * @return string
     */
    public function getTitle(?string $language = null): string
    {
        return array_get($this->translation, 'title', 'Default');
    }

    /**
     * @param string|null $language
     *
     * @return string
     */
    public function getTeaser(?string $language = null): string
    {
        return array_get($this->translation, 'teaser', 'Default');
    }

    /**
     * @param string|null $language
     *
     * @return string
     */
    public function getBody(?string $language = null): string
    {
        return array_get($this->translation, 'body', 'Default');
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
     * This function returns author first and last name
     *
     * @return string
     */
    public function getAuthorName()
    {
        if (empty($this->author)) {
            return trans('gzero-core::common.anonymous');
        }

        return $this->author->getPresenter()->displayName();
    }

    /**
     * This function returns the first img url from provided text
     *
     * @param string $text text to get first image url from
     *
     * @return string first image url
     */
    public function getFirstImageUrl($text)
    {
        $url = null;

        if (!empty($text)) {
            preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $text, $matches);
        }

        if (!empty($matches) && isset($matches[1])) {
            $url = $matches[1];
        }

        return $url;
    }

    /**
     * This function returns the JSON-LD Structured Data Markup for specified language
     *
     * @param string $langCode        translation lang code to get tags for
     * @param string $type            schema.org hierarchy type for the content - 'Article' as default
     * @param array  $imageDimensions optional image dimensions
     *
     * @return string first image url
     */
    public function stDataMarkup($langCode, $type = 'Article', $imageDimensions = ['729', '486'])
    {
        //$html = [];
        //$tags = null;
        //
        //if (!empty($langCode)) {
        //    $translation      = $this->translation($langCode);
        //    $routeTranslation = $this->routeTranslation($langCode);
        //
        //    if (!empty($translation)) {
        //        $firstImageUrl = $this->getFirstImageUrl($translation->teaser);
        //
        //        $tags = [
        //            '@context'         => 'http://schema.org',
        //            '@type'            => $type,
        //            'publisher'        => [
        //                '@type' => 'Brand',
        //                'url'   => routeMl('home'),
        //                'name'  => config('app.name'),
        //                'logo'  => [
        //                    '@type' => 'ImageObject',
        //                    'url'   => asset('/images/logo.png')
        //                ]
        //            ],
        //            'mainEntityOfPage' => [
        //                '@type' => 'WebPage',
        //                '@id'   => routeMl('home')
        //            ],
        //            'headline'         => $translation->title,
        //            'author'           => [
        //                '@type' => "Person",
        //                'name'  => $this->authorName()
        //            ],
        //            'datePublished'    => $this->created_at,
        //            'dateModified'     => $this->updated_at,
        //            'url'              => $this->routeUrl($langCode),
        //        ];
        //
        //        //@TODO add parent categories names
        //        if ($this->level > 0) {
        //            $url = explode('/', $routeTranslation->path);
        //            if ($this->level === '1') {
        //                $tags['articleSection'] = [ucfirst($url[0])];
        //            } else {
        //                $tags['articleSection'] = [ucfirst($url[0]), ucfirst($url[1])];
        //            }
        //        }
        //    }
        //}
        //
        ////@TODO use content thumbnail
        //if (!empty($firstImageUrl)) {
        //    $tags['image'] = [
        //        '@type'  => 'ImageObject',
        //        'url'    => $firstImageUrl,
        //        'width'  => $imageDimensions[0],
        //        'height' => $imageDimensions[1]
        //    ];
        //}
        //
        //if (!empty($tags)) {
        //    $html = [
        //        '<script type="application/ld+json">',
        //        json_encode($tags, true),
        //        '</script>'
        //    ];
        //}
        //
        //return implode('', $html);
    }
}
