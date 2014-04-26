<?php namespace Gzero\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Gzero\Doctrine2Extensions\Common\BaseRepository;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class BlockRepository extends BaseRepository {

    /**
     * Gets all active blocks with translation in specified lang
     *
     * @param Lang $lang Lang model
     *
     * @return mixed
     */
    public function getAllActive(Lang $lang)
    {
        /* @var QueryBuilder $qb */
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from($this->getClassName(), 'b')
            ->leftJoin('b.translations', 't', 'WITH', 't.lang = :lang')
            ->where('b.isActive = 1')
            ->where('b.regions IS NOT NULL')
            ->orderBy('b.weight')
            ->setParameter('lang', $lang->getCode());
        return $qb->getQuery()->getResult();
    }
}
