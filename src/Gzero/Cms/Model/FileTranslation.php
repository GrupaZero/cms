<?php namespace Gzero\Cms\Model;

use Gzero\Base\Model\Base;
use Gzero\Base\Model\Language;

class FileTranslation extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
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
