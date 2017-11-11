<?php namespace Gzero\Cms\Model;

use Gzero\Base\Models\Base;
use Gzero\Base\Models\User;
use Gzero\Cms\Model\Presenter\BlockPresenter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Robbo\Presenter\PresentableInterface;

class Block extends Base implements Uploadable, PresentableInterface {

    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'region',
        'theme',
        'weight',
        'filter',
        'options',
        'is_active',
        'is_cacheable',
    ];

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active'    => false,
        'is_cacheable' => false
    ];

    /**
     * Block type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo(BlockType::class, 'name', 'type');
    }

    /**
     * Translation one to many relation
     *
     * @param bool $active Only active translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations($active = true)
    {
        if ($active) {
            return $this->hasMany(BlockTranslation::class)->where('is_active', '=', 1);
        }
        return $this->hasMany(BlockTranslation::class);
    }

    /**
     * Polymorphic relation to entities that could have relation to block (for example: menu)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function blockable()
    {
        return $this->morphTo();
    }


    /**
     * Get all of the files for the content.
     *
     * @param bool $active Only active file
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphToMany
     */
    public function files($active = true)
    {
        if ($active) {
            return $this->morphToMany(File::class, 'uploadable')->where('is_active', '=', 1)->withPivot('weight')
                ->withTimestamps();
        }
        return $this->morphToMany(File::class, 'uploadable')->withPivot('weight')->withTimestamps();
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
     * It disables active translation for specific language
     *
     * @param string $languageCode language code
     *
     * @return void
     */
    public function disableAllActiveTranslations($languageCode)
    {
        $this->translations()
            ->where('language_code', $languageCode)
            ->update(['is_active' => false]);
    }

    /**
     * Return a created presenter.
     *
     * @return \Robbo\Presenter\Presenter
     */
    public function getPresenter()
    {
        return new BlockPresenter($this);
    }

    /**
     * Set the filter value
     *
     * @param string $value filter value
     *
     * @return string
     */
    public function setFilterAttribute($value)
    {
        return ($value) ? $this->attributes['filter'] = json_encode($value) : $this->attributes['filter'] = null;
    }

    /**
     * Get the filter value
     *
     * @param string $value filter value
     *
     * @return string
     */
    public function getFilterAttribute($value)
    {
        return ($value) ? json_decode($value, true) : $value;
    }

    /**
     * Set the options value
     *
     * @param string $value filter value
     *
     * @return string
     */
    public function setOptionsAttribute($value)
    {
        return ($value) ? $this->attributes['options'] = json_encode($value) : $this->attributes['options'] = null;
    }

    /**
     * Get the options value
     *
     * @param string $value options value
     *
     * @return string
     */
    public function getOptionsAttribute($value)
    {
        return ($value) ? json_decode($value, true) : $value;
    }
}
