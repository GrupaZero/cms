<?php namespace Gzero\Cms\Model;

use Gzero\Base\Model\Base;
use Gzero\Base\Model\Language;

class RouteTranslation extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
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
