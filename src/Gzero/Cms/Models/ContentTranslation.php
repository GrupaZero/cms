<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class ContentTranslation extends Model {

    /** @var array */
    protected $fillable = [
        'language_code',
        'title',
        'teaser',
        'body',
        'seo_title',
        'seo_description',
        'is_active'
    ];

    /** @var array */
    protected $attributes = [
        'is_active' => false
    ];

    /**
     * It converts the created_at date passed to the model to UTC timezone
     *
     * @param \DateTime|string $value dateTime value
     *
     * @return void
     */
    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = dateTimeToUTC($value);
    }

    /**
     * It converts the updated_at date passed to the model to UTC timezone
     *
     * @param \DateTime|string $value dateTime value
     *
     * @return void
     */
    public function setUpdatedAtAttribute($value)
    {
        $this->attributes['updated_at'] = dateTimeToUTC($value);
    }

    /**
     * Content reverse relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Content author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Lang reverse relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
