<?php namespace Gzero\Cms\Models;

use Illuminate\Database\Eloquent\Model;

class ContentType extends Model {

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
