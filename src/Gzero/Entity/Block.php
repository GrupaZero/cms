<?php namespace Gzero\Entity;

use Gzero\Entity\Presenter\BlockPresenter;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Block
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class Block extends Base {

    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'region',
        'theme',
        'authorId',
        'weight',
        'filter',
        'options',
        'isActive',
        'isCacheable',
    ];

    /**
     * Block type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo(BlockType::class, 'name', 'type');
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
            return $this->hasMany(BlockTranslation::class, 'blockId')->where('isActive', '=', 1);
        }
        return $this->hasMany(BlockTranslation::class, 'blockId');
    }

    /**
     * Polymorphic relation to entities that could have relation to block (for example: menu)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function blockable()
    {
        return $this->morphTo();
    }

    /**
     * Block author relation
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
        return new BlockPresenter($this);
    }

    /**
     * Set the filter value
     *
     * @param string $value filter value
     *
     * @return string
     */
    public function setFilterAttribute($value)
    {
        return ($value) ? $this->attributes['filter'] = json_encode($value) : $this->attributes['filter'] = null;
    }

    /**
     * Get the filter value
     *
     * @param string $value filter value
     *
     * @return string
     */
    public function getFilterAttribute($value)
    {
        return ($value) ? json_decode($value, true) : $value;
    }

    /**
     * Set the options value
     *
     * @param string $value filter value
     *
     * @return string
     */
    public function setOptionsAttribute($value)
    {
        return ($value) ? $this->attributes['options'] = json_encode($value) : $this->attributes['options'] = null;
    }

    /**
     * Get the options value
     *
     * @param string $value options value
     *
     * @return string
     */
    public function getOptionsAttribute($value)
    {
        return ($value) ? json_decode($value, true) : $value;
    }
}
