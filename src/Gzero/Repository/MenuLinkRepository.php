<?php namespace Gzero\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Gzero\Doctrine2Extensions\Common\BaseRepository;
use Gzero\Doctrine2Extensions\Tree\TreeRepository;
use Gzero\Doctrine2Extensions\Tree\TreeRepositoryTrait;
use Gzero\Entity\MenuLink;
use Gzero\Entity\Lang;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class MenuLinkRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class MenuLinkRepository extends BaseRepository implements TreeRepository {

    use TreeRepositoryTrait;

    public function getById($id)
    {
        return $this->_em->find($this->getClassName(), $id);
    }


    public function create(MenuLink $menuLink)
    {
        $this->_em->persist($menuLink);
    }

    public function update(MenuLink $menuLink)
    {

    }

    public function delete(MenuLink $menuLink)
    {

    }

    public function save()
    {
        $this->_em->flush();
    }

} 
