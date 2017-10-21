<?php namespace Gzero\Cms\Model;

use Gzero\Base\Model\Base;
use Gzero\Cms\Model\Presenter\ContentTranslationPresenter;
use Robbo\Presenter\PresentableInterface;
use Robbo\Presenter\Robbo;

class ContentTranslation extends Base implements PresentableInterface {

    /**
     * @var array
     */
    protected $fillable = [
        'lang_code',
        'title',
        'teaser',
        'body',
        'seo_title',
        'seo_description',
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
        return $this->belongsTo(Lang::class);
    }

    /**
     * Return a created presenter.
     *
     * @return \Robbo\Presenter\Presenter
     */
    public function getPresenter()
    {
        return new ContentTranslationPresenter($this);
    }
}
