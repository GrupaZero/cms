<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Option
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class Option extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'categoryKey'
    ];

    /**
     * Option category relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(OptionCategory::class, 'categoryKey', 'key');
    }


    /**
     * Set the option value
     *
     * @param string $value option value
     *
     * @return string
     */
    public function setValueAttribute($value)
    {
        // Use json to save lang specific option values
        $this->attributes['value'] = json_encode($value);
    }

    /**
     * Get the option value
     *
     * @param string $value option value
     *
     * @return string
     */
    public function getValueAttribute($value)
    {
        // Decode retrieved value
        return json_decode($value, true);
    }

}
