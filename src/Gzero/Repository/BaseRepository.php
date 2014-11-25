<?php namespace Gzero\Repository;

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
    protected $queryBuilder;

    /**
     * Commit changes to Database
     * NOTICE: This save all entities from current UnityOfWork!
     *
     * @return void
     */
    public function commit()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * Create new query builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQB()
    {
        return $this->queryBuilder->newQuery();
    }

    /**
     * Get entity type class name
     *
     * @return string
     */
    protected function getTypeClassName()
    {
        return $this->getClassName() . 'Type';
    }

    /**
     * Get entity translation class name
     *
     * @return string
     */
    protected function getTranslationClassName()
    {
        return $this->getClassName() . 'Translation';
    }
}
