<?php namespace Gzero\Cms\Models;

use Gzero\Core\Models\Base;

class ContentType extends Base {

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
        'is_active'
    ];

    /**
     * @var array
     */
    protected $attributes = [
        'is_active' => false
    ];
}