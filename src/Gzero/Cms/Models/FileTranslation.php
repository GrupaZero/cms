<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\Language;
use Illuminate\Database\Eloquent\Model;

class FileTranslation extends Model {

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
