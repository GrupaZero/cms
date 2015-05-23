<?php namespace Gzero\Repository;

use BadMethodCallException;
use Gzero\Entity\Base;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
abstract class BaseRepository {

    /**
     * Default number of items per page
     */
    const ITEMS_PER_PAGE = 20;

    /**
     * @var Base
     */
    protected $model;

    /**
     * Eager load relations for eloquent collection.
     * We use this function in handlePagination method!
     *
     * @param Collection $results Eloquent collection
     *
     * @return void
     */
    abstract protected function listEagerLoad($results);

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
     * Get single softDeleted entity
     *
     * @param integer $id Entity id
     *
     * @return mixed
     */
    public function getDeletedById($id)
    {
        return $this->newORMQuery()->onlyTrashed()->find($id);
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
     * Returns eloquent model
     *
     * @return Base
     */
    protected function getEloquentModel()
    {
        return $this->model;
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
     * Add pagination part to query
     *
     * @param string   $defaultTable Default table name
     * @param mixed    $query        Query object
     * @param int|null $page         Page number (if null == disable pagination)
     * @param int|null $pageSize     Limit results
     *
     * @return mixed
     */
    protected function handlePagination($defaultTable, $query, $page, $pageSize)
    {
        if (!empty($page)) { // If we want to paginate result
            $count   = clone $query->getQuery(); // clone the underlying query builder instance, needed for objects with relations
            $results = $query
                ->offset($pageSize * ($page - 1))
                ->limit($pageSize)
                ->get([$defaultTable . '.*']);
            // We only eager load for entry entity
            ($defaultTable === $this->getTableName()) ? $this->listEagerLoad($results) : null;
            return new LengthAwarePaginator($results->all(), $count->select($defaultTable . '.id')->count(), $pageSize, $page);
        } else {
            $results = $query->get([$defaultTable . '.*']);
            // We only eager load for entry entity
            ($defaultTable === $this->getTableName()) ? $this->listEagerLoad($results) : null;
            return $results;
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
     * @throws RepositoryException
     * @return string
     */
    protected function getTranslationsTableName()
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->translations()->getModel()->getTable();
        }
        throw new RepositoryException("Entity '" . get_class($this->model) . "' doesn't have translations relation", 500);
    }

    /**
     * Function sets all translation of provided entity as inactive
     *
     * @param int    $id       entity id
     * @param string $langCode lang code
     *
     * @throws RepositoryException
     * @return bool|int
     */
    protected function disableActiveTranslations($id, $langCode)
    {
        if (method_exists($this->model, 'translations')) {
            $relation   = $this->model->translations();
            $foreignKey = explode('.', $relation->getForeignKey());
            if (isset($foreignKey[1])) {
                return $relation->getModel()
                    ->where($foreignKey[1], $id)
                    ->where('langCode', $langCode)
                    ->update(['isActive' => false]);
            } else {
                throw new RepositoryException("Unable to find foreign key of related translations", 500);
            }
        }
        throw new RepositoryException("Entity '" . get_class($this->model) . "' doesn't have translations relation", 500);
    }


    /**
     * Function returns an unique url address from given url in specific language
     *
     * @param string $url      string url address to search for
     * @param string $langCode translation language
     *
     * @return string $url an unique url address
     */
    protected function buildUniqueUrl($url, $langCode)
    {
        // search for duplicated url
        $count = $this->newQuery()
            ->table('RouteTranslations')
            ->where('langCode', $langCode)
            ->whereRaw("url REGEXP '^$url($|-[0-9]+$)'")
            ->count();
        return ($count) ? $url . '-' . $count : $url;
    }
}
