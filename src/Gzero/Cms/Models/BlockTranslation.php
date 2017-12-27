<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class BlockTranslation extends Model {

    /** @var array */
    protected $fillable = [
        'language_code',
        'title',
        'body',
        'custom_fields',
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

    /**
     * Block reverse relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Block author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    /**
     * Set the options value
     *
     * @param string $value filter value
     *
     * @return string
     */
    public function setCustomFieldsAttribute($value)
    {
        return ($value) ? $this->attributes['custom_fields'] = json_encode($value) : null;
    }

    /**
     * Get the customFields value
     *
     * @param string $value customFields value
     *
     * @return string
     */
    public function getCustomFieldsAttribute($value)
    {
        return ($value) ? json_decode($value, true) : $value;
    }
}
