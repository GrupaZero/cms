<?php

namespace Gzero\Override;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MorphToMany extends \Illuminate\Database\Eloquent\Relations\MorphToMany {

    /**
     * Create a new morph to many relationship instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model   $parent
     * @param string                                $name
     * @param string                                $table
     * @param string                                $foreignKey
     * @param string                                $otherKey
     * @param string                                $relationName
     * @param bool                                  $inverse
     *
     * @return void
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
        $this->inverse    = $inverse;
        $this->morphType  = $name . 'Type';
        $this->morphClass = $inverse ? $query->getModel()->getMorphClass() : $parent->getMorphClass();

        parent::__construct($query, $parent, $table, $foreignKey, $otherKey, $relationName);
    }
}
