<?php namespace Gzero\Entity;

use Gzero\Core\Handler\File\FileTypeHandler;
use Illuminate\Support\Facades\Cache;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockType
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
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
