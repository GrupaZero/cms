<?php namespace Gzero\Cms\Model;

use Gzero\Base\Models\Base;
use Gzero\Base\Models\Language;

class FileTranslation extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'language_code',
        'title',
        'description'
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
