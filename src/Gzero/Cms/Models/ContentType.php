<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\Base;

class ContentType extends Base {

    /** @var array */
    protected $fillable = [
        'name',
        'handler'
    ];

    /**
     * Get content type by name
     *
     * @param string $name Type name
     *
     * @return ContentType
     */
    public static function getByName($name)
    {
        return self::where('name', $name)->first();
    }
}
