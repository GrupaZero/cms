<?php namespace Gzero\Entity\Presenter;

use Carbon\Carbon;
use Robbo\Presenter\Presenter;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Content
 *
 * @package    Gzero\Entity\Presenter
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class ContentPresenter extends Presenter {

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
                    return $translation->langCode === $langCode;
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
                    return $translation->langCode === $langCode;
                }
            )->first();
        }
        return $routeTranslation;
    }

    /**
     * This function returns formatted publish date
     *
     * @return string
     */
    public function publishDate()
    {
        if (!empty($this->publishedAt)) {
            $dt = new Carbon();
            return $dt->parse($this->publishedAt)->format('d-m-Y - H:s');
        } else {
            return trans('common.unknown');
        }
    }

    /**
     * This function returns author first and last name
     *
     * @return string
     */
    public function authorName()
    {
        if (!empty($this->author)) {
            return $this->author->firstName . ' ' . $this->author->lastName;
        } else {
            return trans('common.anonymous');
        }
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
        } else {
            return trans('common.noRatings');
        }
    }

}
