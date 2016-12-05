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
        'file_id',
        'author_id',
        'path',
        'weight',
        'rating',
        'visits',
        'is_on_home',
        'is_comment_allowed',
        'is_promoted',
        'is_sticky',
        'is_active',
        'published_at'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_on_home' => false,
        'is_comment_allowed' => false,
        'is_promoted' => false,
        'is_sticky' => false,
        'is_active' => false
    ];

    /**
     * @var array
     */
    protected $dates = ['published_at', 'deleted_at'];

    /**
     * Get Content url in specified language.
     * WARNING: This function use LAZY LOADING to get this information
     *
     * @param string $lang_code Lang code
     *
     * @return mixed
     * @throws Exception
     */
    public function getUrl($lang_code)
    {
        $routeTranslation = $this->route->translations->filter(
            function ($translation) use ($lang_code) {
                return $translation->lang_code == $lang_code;
            }
        )->first();
        if (empty($routeTranslation->url)) {
            throw new Exception("No route [$lang_code] translation found for Content id: " . $this->getKey());
        }
        return $routeTranslation->url;
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
            return $this->hasMany(ContentTranslation::class)->where('is_active', '=', 1);
        }
        return $this->hasMany(ContentTranslation::class);
    }

    /**
     * Get all of the files for the content.
     *
     * @param bool $active Only active file
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphToMany
     */
    public function files($active = true)
    {
        if ($active) {
            return $this->morphToMany(File::class, 'uploadable')->where('is_active', '=', 1)->withPivot('weight')
                ->withTimestamps();
        }
        return $this->morphToMany(File::class, 'uploadable')->withPivot('weight')->withTimestamps();
    }

    /**
     * Content author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
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
        if (app('auth')->check() && app('auth')->user()->is_admin) {
            return true;
        } else {
            return $this->is_active;
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
