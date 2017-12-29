<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\User;
use Gzero\Cms\ViewModels\BlockViewModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Gzero\InvalidArgumentException;
use Robbo\Presenter\PresentableInterface;

class Block extends Model implements Uploadable, PresentableInterface {

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
        return $this->belongsTo(BlockType::class);
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
            return $this->hasMany(BlockTranslation::class)->where('is_active', '=', true);
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
     * Function sets all content translations in provided language code as inactive
     *
     * @param string $languageCode language code
     *
     * @return mixed
     */
    public function disableActiveTranslations($languageCode)
    {
        return $this->translations()
            ->where('block_id', $this->id)
            ->where('language_code', $languageCode)
            ->update(['is_active' => false]);
    }

    /**
     * @param string $type Block type
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function setTypeAttribute($type)
    {
        if (!$type instanceof BlockType) {
            $type = BlockType::getByName($type);
        }
        if (!$type) {
            throw new InvalidArgumentException('Unknown block type');
        }
        $this->type()->associate($type);
    }

    /**
     * Returns active translation in specific language
     *
     * @param string $languageCode Language code
     *
     * @return mixed
     */
    public function getActiveTranslation($languageCode)
    {
        return $this->translations->first(function ($translation) use ($languageCode) {
            return $translation->is_active === true && $translation->language_code === $languageCode;
        });
    }

    /**
     * Return a created presenter.
     *
     * @return BlockViewModel
     */
    public function getPresenter()
    {
        return new BlockViewModel($this->toArray());
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

    /**
     * Check if entity exists
     *
     * @param int $id entity id
     *
     * @return boolean
     */
    public static function checkIfExists($id)
    {
        return self::where('id', $id)->exists();
    }
}
