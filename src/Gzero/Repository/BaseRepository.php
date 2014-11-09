<?php namespace Gzero\Repository;

use Gzero\Doctrine2Extensions\Common\BaseRepository as CommonBaseRepository;

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
class BaseRepository extends CommonBaseRepository {

    /**
     * Create new query builder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function newQB()
    {
        return $this->_em->createQueryBuilder();
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
