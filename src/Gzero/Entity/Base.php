<?php namespace Gzero\Entity;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class Base
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
abstract class Base extends Model {

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

    /**
     * The name of the "deleted at" column.
     *
     * @var string
     */
    const DELETED_AT = 'deletedAt';


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

    /**
     * Get the polymorphic relationship columns.
     *
     * @param string $name name
     * @param string $type type
     * @param string $id   id
     *
     * @return array
     */
    protected function getMorphs($name, $type, $id)
    {
        $type = $type ?: $name . 'Type';

        $id = $id ?: $name . 'Id';

        return [$type, $id];
    }
}
