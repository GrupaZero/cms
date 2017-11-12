<?php namespace Gzero\Cms\Model;

use Gzero\Core\Models\Base;
use Gzero\Core\Models\Language;

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
