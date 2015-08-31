<?php namespace Gzero\Entity;

use Gzero\Core\Exception;
use Gzero\Entity\Presenter\ContentPresenter;
use Robbo\Presenter\PresentableInterface;
use Robbo\Presenter\Robbo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Content
 *
 * @package    Gzero\Model
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Content extends BaseTree implements PresentableInterface {

    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'authorId',
        'path',
        'weight',
        'rating',
        'visits',
        'isOnHome',
        'isCommentAllowed',
        'isPromoted',
        'isSticky',
        'isActive',
        'publishedAt'
    ];

    /**
     * Get Content url in specified language.
     * WARNING: This function use LAZY LOADING to get this information
     *
     * @param string $langCode Lang code
     *
     * @return mixed
     * @throws Exception
     */
    public function getUrl($langCode)
    {
        $routeTranslation = $this->route->translations->filter(
            function ($translation) use ($langCode) {
                return $translation->langCode == $langCode;
            }
        )->first();
        if (!empty($routeTranslation->url)) {
            return $routeTranslation->url;
        } else {
            throw new Exception("No route [$langCode] translation found for Content id: " . $this->getKey(), 500);
        }
    }


    /**
     * Content type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo('\Gzero\Entity\ContentType', 'name', 'type');
    }

    /**
     * Polymorphic relation with route
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function route()
    {
        return $this->morphOne('\Gzero\Entity\Route', 'routable', 'routableType', 'routableId');
    }

    /**
     * Translation one to many relation
     *
     * @param bool $active Only active translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations($active = true)
    {
        if ($active) {
            return $this->hasMany('\Gzero\Entity\ContentTranslation', 'contentId')->where('isActive', '=', 1);
        }
        return $this->hasMany('\Gzero\Entity\ContentTranslation', 'contentId');
    }

    /**
     * Content author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo('\Gzero\Entity\User', 'authorId', 'id');
    }

    /**
     * Return a created presenter.
     *
     * @return \Robbo\Presenter\Presenter
     */
    public function getPresenter()
    {
        return new ContentPresenter($this);
    }


    /**
     * Return true if content can be shown to current user on front end
     *
     * @return bool
     */
    public function canBeShown()
    {
        $isAdmin = (app('auth')->check() && app('auth')->user()->isAdmin ? true : false);
        return ($this->isActive || $isAdmin ? true : false);
    }
}
