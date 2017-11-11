<?php namespace Gzero\Cms\Model;

use Gzero\Base\Models\Base;
use Gzero\Base\Models\Language;

class RouteTranslation extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'language_code',
        'url',
        'is_active'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active' => false
    ];

    /**
     * Lang reverse relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lang()
    {
        return $this->belongsTo(Language::class);
    }
}
