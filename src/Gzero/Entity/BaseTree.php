<?php namespace Gzero\Entity;

use Gzero\EloquentTree\Model\Tree;
use Carbon\Carbon;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BaseTree
 *
 * @package    Gzero\Model
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
abstract class BaseTree extends Tree {

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createdAt';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updatedAt';

    // @codingStandardsIgnoreStart

    /**
     * Database mapping tree fields
     *
     * @var Array
     */
    protected static $treeColumns = [
        'path'   => 'path',
        'parent' => 'parentId',
        'level'  => 'level'
    ];

    // @codingStandardsIgnoreEnd

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->table;
        }

        return str_replace('\\', '', ucfirst(camel_case(str_plural(class_basename($this)))));
    }

    /**
     * Default accessor do createdAt
     *
     * @param string $date Date string
     *
     * @return string
     */
    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->toIso8601String();
    }

    /**
     * Default accessor do updatedAt
     *
     * @param string $date Date string
     *
     * @return string
     */
    public function getUpdatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->toIso8601String();
    }
}
