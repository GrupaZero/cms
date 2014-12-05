<?php namespace Gzero\Repository;

use BadMethodCallException;
use Gzero\Doctrine2Extensions\Common\BaseRepository as CommonBaseRepository;
use Gzero\Entity\Base;

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
     * Create new query builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQB()
    {
        return $this->model->newQuery();
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
            throw new RepositoryException("Relation '" . $relationString . "' does not exist", 500);
        }

    }
}
