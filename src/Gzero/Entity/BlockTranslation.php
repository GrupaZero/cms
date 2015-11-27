<?php namespace Gzero\Entity;

use Robbo\Presenter\PresentableInterface;
use Robbo\Presenter\Robbo;
use Gzero\Entity\Presenter\BlockTranslationPresenter;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockTranslation
 *
 * @package    Gzero\Model
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class BlockTranslation extends Base implements PresentableInterface {

    /**
     * @var array
     */
    protected $fillable = [
        'langCode',
        'title',
        'body',
        'customFields',
        'isActive'
    ];

    /**
     * Lang reverse relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lang()
    {
        return $this->belongsTo('\Gzero\Entity\Lang');
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
        $this->attributes['customFields'] = json_encode($value);
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
        return json_decode($value, true);
    }
}