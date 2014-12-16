<?php namespace Gzero\Entity;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Content
 *
 * @package    Gzero\Model
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class Content extends BaseTree {

    protected $fillable = [
        'type',
        'authorId',
        'path',
        'weight',
        'isActive'
    ];

    /**
     * Content type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo('\Gzero\Entity\ContentType', 'name', 'type');
    }

    /**
     * Polymorphic relation with route
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function route()
    {
        return $this->morphOne('\Gzero\Entity\Route', 'routable', 'routableType', 'routableId');
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
            return $this->hasMany('\Gzero\Entity\ContentTranslation', 'contentId')->where('isActive', '=', 1);
        }
        return $this->hasMany('\Gzero\Entity\ContentTranslation', 'contentId');
    }

    /**
     * Content author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo('\Gzero\Entity\User', 'authorId', 'id');
    }

}
