<?php namespace Gzero\Entity;

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
        'isActive'
    ];

    /**
     * Return list of active types.
     *
     * @return array
     */
    public function getActiveTypes()
    {
        $types = Cache::rememberForever(
            'fileTypes',
            function () {
                return array_pluck($this->where('isActive', true)->get(['name'])->toArray(), 'name');
            }
        );

        return $types;
    }
}
