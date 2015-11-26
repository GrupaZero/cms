<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Block
 *
 * @package    Gzero\Model
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class Block extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'region',
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
        return $this->belongsTo('\Gzero\Entity\BlockType', 'name', 'type');
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
            return $this->hasMany('\Gzero\Entity\BlockTranslation', 'blockId')->where('isActive', '=', 1);
        }
        return $this->hasMany('\Gzero\Entity\BlockTranslation', 'blockId');
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
        return $this->belongsTo('\Gzero\Entity\User', 'authorId', 'id');
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
        $this->attributes['filter'] = json_encode($value);
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
        return json_decode($value, true);
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
        $this->attributes['options'] = json_encode($value);
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
        return json_decode($value, true);
    }
}
