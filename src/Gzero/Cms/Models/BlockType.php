<?php namespace Gzero\Cms\Models;

use Illuminate\Database\Eloquent\Model;

class BlockType extends Model {

    /** @var array */
    protected $fillable = [
        'name',
        'handler'
    ];

    /**
     * Get block type by name
     *
     * @param string $name Type name
     *
     * @return BlockType
     */
    public static function getByName($name)
    {
        return self::where('name', $name)->first();
    }
}
