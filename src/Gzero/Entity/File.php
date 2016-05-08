<?php namespace Gzero\Entity;

use Gzero\Entity\Presenter\FilePresenter;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class File
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class File extends Base {

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'name',
        'extension',
        'size',
        'mimeType',
        'info',
        'createdBy',
        'isActive'
    ];

    /**
     * File type relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function type()
    {
        return $this->belongsTo(FileType::class, 'name', 'type');
    }

    /**
     * Translation one to many relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(FileTranslation::class, 'fileId');
    }

    /**
     * Get all of the contents that are assigned this file.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function contents()
    {
        return $this->morphedByMany(Content::class, 'uploadable')->withTimestamps();
    }

    /**
     * Get all of the contents that are assigned this file.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function blocks()
    {
        return $this->morphedByMany(Block::class, 'uploadable')->withTimestamps();
    }

    /**
     * File author relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'createdBy', 'id');
    }

    /**
     * Return a created presenter.
     *
     * @return \Robbo\Presenter\Presenter
     */
    public function getPresenter()
    {
        return new FilePresenter($this);
    }

    /**
     * Set the info value
     *
     * @param string $value info value
     *
     * @return string
     */
    public function setInfoAttribute($value)
    {
        return ($value) ? $this->attributes['info'] = json_encode($value) : $this->attributes['info'] = null;
    }

    /**
     * Get the info value
     *
     * @param string $value info value
     *
     * @return string
     */
    public function getInfoAttribute($value)
    {
        return ($value) ? json_decode($value, true) : $value;
    }


}
