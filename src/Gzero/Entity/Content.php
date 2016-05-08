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
 * @package    Gzero\Entity
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
        'theme',
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
            throw new Exception("No route [$langCode] translation found for Content id: " . $this->getKey());
        }
    }

    /**
     * Content type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo(ContentType::class, 'name', 'type');
    }

    /**
     * Polymorphic relation with route
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function route()
    {
        return $this->morphOne(Route::class, 'routable');
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
            return $this->hasMany(ContentTranslation::class, 'contentId')->where('isActive', '=', 1);
        }
        return $this->hasMany(ContentTranslation::class, 'contentId');
    }

    /**
     * Get all of the files for the content.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphToMany
     */
    public function files()
    {
        return $this->morphToMany(File::class, 'uploadable');
    }

    /**
     * Content author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'authorId', 'id');
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
        if (app('auth')->check() && app('auth')->user()->isAdmin) {
            return true;
        } else {
            return $this->isActive;
        }
    }

    /**
     * Find all trashed descendants for specific node with this node as root
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function findDescendantsWithTrashed()
    {
        return static::withTrashed()->where($this->getTreeColumn('path'), 'LIKE', $this->{$this->getTreeColumn('path')} . '%')
            ->orderBy($this->getTreeColumn('level'), 'ASC');
    }
}
