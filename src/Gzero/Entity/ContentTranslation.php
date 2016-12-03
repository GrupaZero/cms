<?php namespace Gzero\Entity;

use Robbo\Presenter\PresentableInterface;
use Robbo\Presenter\Robbo;
use Gzero\Entity\Presenter\ContentTranslationPresenter;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ContentTranslation
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class ContentTranslation extends Base implements PresentableInterface {

    /**
     * @var array
     */
    protected $fillable = [
        'langCode',
        'title',
        'teaser',
        'body',
        'seoTitle',
        'seoDescription',
        'isActive'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'isActive' => false
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
