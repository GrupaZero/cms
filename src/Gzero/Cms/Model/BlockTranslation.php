<?php namespace Gzero\Cms\Model;

use Gzero\Base\Models\Base;
use Gzero\Base\Models\Language;
use Gzero\Cms\Model\Presenter\BlockTranslationPresenter;
use Robbo\Presenter\PresentableInterface;
use Robbo\Presenter\Robbo;

class BlockTranslation extends Base implements PresentableInterface {

    /**
     * @var array
     */
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
     * Return a created presenter.
     *
     * @return \Robbo\Presenter\Presenter
     */
    public function getPresenter()
    {
        return new BlockTranslationPresenter($this);
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
