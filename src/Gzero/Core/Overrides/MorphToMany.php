<?php

namespace Gzero\Core\Overrides;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MorphToMany extends \Illuminate\Database\Eloquent\Relations\MorphToMany {

    /**
     * Create a new morph to many relationship instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query        query string
     * @param \Illuminate\Database\Eloquent\Model   $parent       parent model
     * @param string                                $name         name
     * @param string                                $table        table name
     * @param string                                $foreignKey   foreign key
     * @param string                                $otherKey     other key
     * @param string                                $relationName relation name
     * @param bool                                  $inverse      whether it should be reversed
     *
     * @SuppressWarnings(PHPMD)
     */
    public function __construct(
        Builder $query,
        Model $parent,
        $name,
        $table,
        $foreignKey,
        $otherKey,
        $relationName = null,
        $inverse = false
    ) {
        parent::__construct(
            $query,
            $parent,
            $name,
            $table,
            $foreignKey,
            $otherKey,
            $relationName = null,
            $inverse = false
        );
        $this->morphType = $name . 'Type';
    }
}
