<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\Base;
use Gzero\Core\Handler\File\FileTypeHandler;
use Illuminate\Support\Facades\Cache;

class FileType extends Base {

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string
     */
    protected $primaryKey = 'name';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'extensions',
        'is_active'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active' => false
    ];

    /**
     * Return list of active types.
     *
     * @return array
     */
    public function getActiveTypes()
    {
        $types = Cache::rememberForever(
            'file_types',
            function () {
                return array_pluck($this->where('is_active', true)->get(['name'])->toArray(), 'name');
            }
        );

        return $types;
    }

    /**
     * Dynamically resolve type of content
     *
     * @param String $typeName Type name
     *
     * @return FileTypeHandler
     * @throws \ReflectionException
     */
    public function resolveType($typeName)
    {
        $type = app()->make('file:type:' . $typeName);
        if (!$type instanceof FileTypeHandler) {
            throw new \ReflectionException("Type: $typeName must implement FileTypeInterface");
        }
        return $type;
    }
}
