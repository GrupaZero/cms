<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\Base;
use Gzero\Core\Models\User;
use Gzero\Cms\Models\Presenter\FilePresenter;
use Illuminate\Support\Collection;
use Robbo\Presenter\PresentableInterface;

class File extends Base implements PresentableInterface {

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'name',
        'extension',
        'size',
        'mime_type',
        'info',
        'createdBy',
        'is_active'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active' => false
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
        return $this->hasMany(FileTranslation::class, 'file_id');
    }

    /**
     * Get all of the contents that are assigned this file.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contents()
    {
        return $this->morphedByMany(Content::class, 'uploadable')->withTimestamps();
    }

    /**
     * Get all of the blocks that are assigned this file.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
        return $this->belongsTo(User::class, 'created_by', 'id');
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
     * Return file name with extension
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->name . '.' . $this->extension;
    }

    /**
     * Returns file upload path based on file type plural name e.g. 'images', 'documents'
     *
     * @return string
     */
    public function getUploadPath()
    {
        return str_plural($this->type) . '/';
    }

    /**
     * Returns file public url
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->getUploadPath() . $this->getFileName();
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

    /**
     * Check if multiple files exists
     *
     * @param array $filesIds array with file ids
     *
     * @return Collection
     */
    public static function checkIfMultipleExists($filesIds)
    {
        $idsInDb = self::whereIn('id', $filesIds)->pluck('id');
        return collect($filesIds)->diff($idsInDb);
    }
}
