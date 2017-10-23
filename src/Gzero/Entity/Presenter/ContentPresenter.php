<?php namespace Gzero\Entity\Presenter;

use Illuminate\Support\Facades\File;
use function Symfony\Component\Debug\Tests\testHeader;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ContentPresenter
 *
 * @package    Gzero\Entity\Presenter
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class ContentPresenter extends BasePresenter {

    /**
     * This function get single translation
     *
     * @param string $langCode LangCode
     *
     * @return string
     */
    public function translation($langCode)
    {
        $translation = '';
        if (!empty($this->translations) && !empty($langCode)) {
            $translation = $this->translations->filter(
                function ($translation) use ($langCode) {
                    return $translation->lang_code === $langCode;
                }
            )->first();
        }
        return $translation;
    }

    /**
     * This function get single route translation
     *
     * @param string $langCode LangCode
     *
     * @return string
     */
    public function routeTranslation($langCode)
    {
        $routeTranslation = '';
        if (!empty($this->route) && !empty($langCode)) {
            $routeTranslation = $this->route->translations->filter(
                function ($translation) use ($langCode) {
                    return $translation->lang_code === $langCode;
                }
            )->first();
        }
        return $routeTranslation;
    }

    /**
     * This function get single route url
     *
     * @param string $langCode LangCode
     *
     * @return string
     */
    public function routeUrl($langCode)
    {
        $routeUrl = '';
        if (!empty($this->route) && !empty($langCode)) {
            $route = $this->route->translations->filter(
                function ($translation) use ($langCode) {
                    return $translation->lang_code === $langCode;
                }
            )->first();

            if (!empty($route)) {
                if (config('gzero.multilang.enabled')) {
                    $routeUrl = url('/') . '/' . $route->lang_code . '/' . $route->url;
                } else {
                    $routeUrl = url('/') . '/' . $route->url;
                }
            }
        }
        return $routeUrl;
    }

    /**
     * This function returns formatted publish date
     *
     * @return string
     */
    public function publishDate()
    {
        if (!empty($this->published_at)) {
            return $this->published_at;
        }
        return trans('common.unknown');
    }

    /**
     * This function returns author first and last name
     *
     * @return string
     */
    public function authorName()
    {
        if (!empty($this->author)) {
            return $this->author->getPresenter()->displayName();
        }
        return trans('common.anonymous');
    }

    /**
     * This function returns the star rating
     *
     * @return string html containing star icons
     */
    public function ratingStars()
    {
        if (!empty($this->rating)) {
            $html = [];
            for ($i = 0; $i < 5; $i++) {
                if ($i < $this->rating && $this->rating > 0) {
                    $html[] = '<i class="glyphicon glyphicon-star"></i> ';
                } else {
                    $html[] = '<i class="glyphicon glyphicon-star-empty"></i> ';
                }
            }
            return implode('', $html);
        }
        return trans('common.no_ratings');
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
        $html = [];
        $tags = null;

        if (!empty($langCode)) {
            $translation      = $this->translation($langCode);
            $routeTranslation = $this->routeTranslation($langCode);

            if (!empty($translation)) {
                $tags = [
                    '@context'         => 'http://schema.org',
                    '@type'            => $type,
                    'publisher'        => [
                        '@type' => 'Organization',
                        'url'   => route('home'),
                        'name'  => config('app.name'),
                        'logo'  => [
                            '@type' => 'ImageObject',
                            'url'   => asset('/images/share-logo.png')
                        ]
                    ],
                    'mainEntityOfPage' => [
                        '@type' => 'WebPage',
                        '@id'   => route('home')
                    ],
                    'headline'         => $translation->title,
                    'author'           => [
                        '@type' => "Person",
                        'name'  => $this->authorName()
                    ],
                    'datePublished'    => $this->created_at,
                    'dateModified'     => $this->updated_at->toDateTimeString(),
                    'url'              => $this->routeUrl($langCode),
                ];

                //@TODO add parent categories names
                if ($this->level > 0) {
                    $url = explode('/', $routeTranslation->url);
                    if ($this->level === '1') {
                        $tags['articleSection'] = [ucfirst($url[0])];
                    } else {
                        $tags['articleSection'] = [ucfirst($url[0]), ucfirst($url[1])];
                    }
                }
            }
        }

        $tags = $tags + $this->getImageObjectStDataMarkup($imageDimensions, $langCode);

        if (!empty($tags)) {
            $html = [
                '<script type="application/ld+json">',
                json_encode($tags, true),
                '</script>'
            ];
        }

        return implode('', $html);
    }

    /**
     * Returns the JSON-LD Structured Data Markup for ImageObject type
     *
     * @param array  $dimensions image dimensions
     * @param string $langCode   translation lang code to get tags for
     *
     * @return array
     */
    public function getImageObjectStDataMarkup(array $dimensions, string $langCode)
    {
        $translation      = $this->translation($langCode);
        $firstImageUrl = !empty($translation) ? $this->getFirstImageUrl($translation->teaser) : null;

        $tags['image'] = [
            '@type'  => 'ImageObject',
            'width'  => isset($dimensions[0]) ? $dimensions[0] : config('gzero.image.thumb.width'),
            'height' => isset($dimensions[1]) ? $dimensions[1] : 'auto'
        ];
        
        if (!empty($this->thumb)) {
            $tags['image']['url'] = asset(croppaUrl($this->thumb->getFullPath(),
                config('gzero.image.thumb.width'), config('gzero.image.thumb.height'), ['resize']));
        } elseif (!empty($firstImageUrl)) {
            $tags['image']['url'] = $firstImageUrl;
        } elseif (File::exists(base_path('public/images/share-logo.png'))) {
            $tags['image']['url'] = asset('images/share-logo.png');
        } else {
            $tags['image']['url'] = asset('gzero/cms/img/share-logo.png');
        }

        return $tags;
    }

}
