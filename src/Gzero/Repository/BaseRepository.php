<?php namespace Gzero\Repository;

use BadMethodCallException;
use Gzero\Entity\Base;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BaseRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class BaseRepository {

    /**
     * Default number of items per page
     */
    const ITEMS_PER_PAGE = 20;

    /**
     * @var Base
     */
    protected $model;

    /**
     * Get single entity
     *
     * @param integer $id Entity id
     *
     * @return mixed
     */
    public function getById($id)
    {
        return $this->newORMQuery()->find($id);
    }

    /**
     * Create new ORM query builder
     *
     * @return Builder
     */
    protected function newORMQuery()
    {
        return $this->model->newQuery();
    }

    /**
     * Create new query builder
     *
     * @return Connection
     */
    protected function newQuery()
    {
        return App::make('db');
    }

    /**
     * Add filter rules to query
     *
     * @param string $entityTableName Base entity name (for example: Contents, Blocks, BlockTranslations etc.)
     * @param array  $criteria        Array with filer criteria
     * @param mixed  $query           Query to add filter rules
     *
     * @throws RepositoryException
     * @return void
     */
    protected function handleFilterCriteria($entityTableName, array $criteria, $query)
    {
        $conditions = [];
        foreach ($criteria as $condition => $value) {
            $conditions[] = $query->where(
                $this->resolveTableName($entityTableName, $value['relation'], $query) . $condition,
                '=',
                $value['value']
            );
        }
    }

    /**
     * Add sorting rules to query
     *
     * @param string   $entityTableName Base entity name (for example: Contents, Blocks, BlockTranslations etc.)
     * @param array    $orderBy         Array with sort columns and directions
     * @param mixed    $query           Query to add sorting rules
     * @param callable $defaultOrder    Function with default order
     *
     * @throws RepositoryException
     * @return void
     */
    protected function handleOrderBy($entityTableName, array $orderBy, $query, $defaultOrder = null)
    {
        if (empty($orderBy) && is_callable($defaultOrder)) { // Default order
            $defaultOrder($query);
        }
        foreach ($orderBy as $sort => $order) {
            $query->orderBy(
                $this->resolveTableName($entityTableName, $order['relation'], $query) . $sort,
                $order['direction']
            );
        }
    }

    /**
     * Resolve name of Table for provided relation string
     *
     * @param string $defaultTable   name of the default model relation
     * @param string $relationString name of the model relation
     * @param mixed  $query          Eloquent query object
     *
     * @throws RepositoryException
     * @return string
     */
    protected function resolveTableName($defaultTable, $relationString, $query)
    {
        try {
            if (!empty($relationString)) {
                $lastRelation = $query;
                foreach (explode('.', $relationString) as $relationName) {
                    $lastRelation = $lastRelation->getRelation($relationName);
                }
                return $lastRelation->getModel()->getTable() . '.';
            }
            return $defaultTable . '.';
        } catch (BadMethodCallException $e) {
            throw new RepositoryException("Relation '" . $relationString . "' doesn't exist", 500);
        }

    }


    /**
     * Returns model table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return $this->model->getTable();
    }

    /**
     * Returns translations model table name
     *
     * @return string
     * @throws RepositoryException
     */
    protected function getTranslationsTableName()
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->translations()->getModel()->getTable();
        }
        throw new RepositoryException("Entity '" . get_class($this->model) . "' doesn't have translations relation", 500);
    }
}
