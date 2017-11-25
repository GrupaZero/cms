<?php namespace Gzero\Cms\Models;

use Gzero\Cms\Presenters\ContentTranslationPresenter;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Robbo\Presenter\PresentableInterface;
use Robbo\Presenter\Robbo;

class ContentTranslation extends Model implements PresentableInterface {

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
