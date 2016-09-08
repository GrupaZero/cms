<?php namespace Gzero\Repository;

use BadMethodCallException;
use Gzero\Entity\Base;
use Gzero\Entity\File;
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
     * Get single softDeleted entity
     *
     * @param integer $id Entity id
     *
     * @return mixed
     */
    public function getByIdWithTrashed($id)
    {
        return $this->newORMQuery()->withTrashed()->find($id);
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
     * @param mixed  $query           Query to add filter rules
     * @param array  $criteria        Array with filer criteria
     *
     * @throws RepositoryException
     * @return void
     */
    protected function handleFilterCriteria($entityTableName, $query, array $criteria = [])
    {
        $conditions = [];
        foreach ($criteria as $condition => $value) {
            if ($value['value'] === null) {
                if ($value['operation'] === '=') {
                    $conditions[] = $query->whereNull(
                        $this->resolveTableName($entityTableName, $value['relation'], $query) . $condition
                    );
                }
                if ($value['operation'] === '!=') {
                    $conditions[] = $query->whereNotNull(
                        $this->resolveTableName($entityTableName, $value['relation'], $query) . $condition
                    );
                }
            } else {
                $conditions[] = $query->where(
                    $this->resolveTableName($entityTableName, $value['relation'], $query) . $condition,
                    $value['operation'],
                    $value['value']
                );
            }
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
            throw new RepositoryException("Relation '" . $relationString . "' doesn't exist");
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
        throw new RepositoryException("Entity '" . get_class($this->model) . "' doesn't have translations relation");
    }

    /**
     * Returns files model table name
     *
     * @throws RepositoryException
     * @return string
     */
    protected function getFilesTableName()
    {
        if (method_exists($this->model, 'files')) {
            return $this->model->files()->getModel()->getTable();
        }
        throw new RepositoryException("Entity '" . get_class($this->model) . "' doesn't have files relation");
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
                throw new RepositoryException("Unable to find foreign key of related translations");
            }
        }
        throw new RepositoryException("Entity '" . get_class($this->model) . "' doesn't have translations relation");
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

    /**
     * This functions translate filter criteria and orderBy params to more extended version.
     *
     * @param array $criteria Filter criteria
     * @param array $orderBy  Array of columns
     *
     * @return array
     */
    protected function parseArgs($criteria, $orderBy)
    {
        return [
            'filter'  => $this->parseCriteria($criteria),
            'orderBy' => $this->parseOrderBy($orderBy)
        ];
    }

    /**
     * This functions checks if files exists in database
     *
     * @param array $filesIds array of file id's to check for
     *
     * @return array $filesIds array of file id's
     * @throws RepositoryValidationException
     */
    protected function checkIfFilesExists(Array $filesIds)
    {
        foreach ($filesIds as $fileId) {
            if (!File::checkIfExists($fileId)) {
                throw new RepositoryValidationException("File (id: $fileId) does not exist");
            }
        }

        return $filesIds;
    }

    /**
     * Parse criteria to extended version
     *
     * @param array $criteria Filter criteria
     *
     * @return array
     */
    private function parseCriteria($criteria)
    {
        $result = [];
        foreach ($criteria as $row) {
            if (preg_match('/\./', $row[0])) {
                $temp = explode('.', $row[0]);

                $result[array_pop($temp)] = [
                    'value'     => (is_numeric($row[2])) ? (float) $row[2] : $row[2],
                    'operation' => $row[1],
                    'relation'  => trim(implode('.', $temp), '.')
                ];

            } else {
                $result[$row[0]] = [
                    'value'     => (is_numeric($row[2])) ? (float) $row[2] : $row[2],
                    'operation' => $row[1],
                    'relation'  => null
                ];
            }
        }
        return $result;
    }

    /**
     * Parse orderBy to extended version
     *
     * @param array $orderBy Array of columns
     *
     * @return array
     */
    private function parseOrderBy($orderBy)
    {
        $result = [];
        foreach ($orderBy as $row) {
            if (preg_match('/\./', $row[0])) {
                $temp                     = explode('.', $row[0]);
                $result[array_pop($temp)] = [
                    'direction' => $row[1],
                    'relation'  => trim(implode('.', $temp), '.')
                ];
            } else {
                $result[$row[0]] = [
                    'direction' => $row[1],
                    'relation'  => null
                ];
            }
        }
        return $result;
    }
}
