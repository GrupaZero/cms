<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Widget
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class Widget extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'args',
        'is_active',
        'is_cacheable',
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active' => false,
        'is_cacheable' => false
    ];

    /**
     * Block polymorphic  relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function blocks()
    {
        return $this->morphMany(Block::class, 'blockable');
    }

    /**
     * Set the args value
     *
     * @param string $value args value
     *
     * @return string
     */
    public function setArgsAttribute($value)
    {
        return ($value) ? $this->attributes['args'] = json_encode($value) : null;
    }

    /**
     * Get the args value
     *
     * @param string $value args value
     *
     * @return string
     */
    public function getArgsAttribute($value)
    {
        return ($value) ? json_decode($value, true) : $value;
    }

}
