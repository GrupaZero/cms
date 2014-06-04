<?php namespace Gzero\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Gzero\Doctrine2Extensions\Common\BaseRepository;
use Gzero\Doctrine2Extensions\Tree\TreeRepository;
use Gzero\Doctrine2Extensions\Tree\TreeRepositoryTrait;
use Gzero\Entity\Content;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ContentRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class ContentRepository extends BaseRepository implements TreeRepository {

    use TreeRepositoryTrait;

    public function getById($id)
    {
        return $this->_em->find($this->getClassName(), $id);
    }

    public function getTypeById($id)
    {
        return $this->_em->find($this->getClassName() . 'Type', $id);
    }

    public function getByUrl($url, Lang $lang)
    {
        /* @var QueryBuilder $qb */
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c')
            ->from($this->getClassName(), 'c')
            ->leftJoin('c.translations', 't', 'WITH', 't.lang = :lang')
            ->where('t.url = :url')
            ->andWhere('c.isActive = 1')
            ->orderBy('c.weight')
            ->setParameter('lang', $lang->getCode())
            ->setParameter('url', $url);
        $result = $qb->getQuery()->getResult();
        return (!empty($result)) ? $result[0] : $result;
    }

    public function create(Content $content)
    {
        $this->_em->persist($content);
    }

    public function update(Content $content)
    {

    }

    public function delete(Content $content)
    {

    }

    public function save()
    {
        $this->_em->flush();
    }
}
