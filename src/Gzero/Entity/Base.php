<?php namespace Gzero\Entity;

use Gzero\Core\Overrides\MorphToMany;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return snake_case(class_basename($this)) . 'Id';
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

    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @param string $related    related model
     * @param string $name       relation name
     * @param string $table      table name
     * @param string $foreignKey foreign key
     * @param string $otherKey   other key
     * @param bool   $inverse    if use reversed relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     * @SuppressWarnings(PHPMD)
     */
    public function morphToMany($related, $name, $table = null, $foreignKey = null, $otherKey = null, $inverse = false)
    {
        $caller = $this->getBelongsToManyCaller();

        // First, we will need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we will make the query
        // instances, as well as the relationship instances we need for these.
        $foreignKey = $foreignKey ?: $name . 'Id';

        $instance = new $related;

        $otherKey = $otherKey ?: $instance->getForeignKey();

        // Now we're ready to create a new query builder for this related model and
        // the relationship instances for this relation. This relations will set
        // appropriate query constraints then entirely manages the hydrations.
        $query = $instance->newQuery();

        $table = $table ?: ucfirst(Str::plural($name));

        return new MorphToMany($query, $this, $name, $table, $foreignKey, $otherKey, $caller, $inverse);
    }

    /**
     * Define a polymorphic, inverse many-to-many relationship.
     *
     * @param string $related    related model
     * @param string $name       relation name
     * @param string $table      table name
     * @param string $foreignKey foreign key
     * @param string $otherKey   other key
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function morphedByMany($related, $name, $table = null, $foreignKey = null, $otherKey = null)
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        // For the inverse of the polymorphic many-to-many relations, we will change
        // the way we determine the foreign and other keys, as it is the opposite
        // of the morph-to-many method since we're figuring out these inverses.
        $otherKey = $otherKey ?: $name . 'Id';

        return $this->morphToMany($related, $name, $table, $foreignKey, $otherKey, true);
    }
}
