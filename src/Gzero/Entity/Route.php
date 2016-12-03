<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Route
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Route extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'isActive'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'isActive' => false
    ];

    /**
     * Polymorphic relation to entities that could have route
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function routable()
    {
        return $this->morphTo();
    }

    /**
     * Translation one to many relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(RouteTranslation::class, 'routeId');
    }
}
