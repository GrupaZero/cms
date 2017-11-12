<?php namespace Gzero\Cms\Model;

use Gzero\Core\Models\Base;

class Route extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'is_active'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active' => false
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
        return $this->hasMany(RouteTranslation::class);
    }
}
