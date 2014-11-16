<?php namespace Gzero\Model;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Route
 *
 * @package    Gzero\Model
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Route extends Base {

    protected $fillable = [
        'isActive'
    ];

    /**
     * Translation one to many relation
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
        return $this->hasMany('\Gzero\Model\RouteTranslation', 'routeId');
    }
}
