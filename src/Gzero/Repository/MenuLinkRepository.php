<?php namespace Gzero\Repository;

use Doctrine\ORM\Query;
use Gzero\Doctrine2Extensions\Tree\TreeNode;
use Gzero\Doctrine2Extensions\Tree\TreeRepository;
use Gzero\Doctrine2Extensions\Tree\TreeRepositoryTrait;
use Gzero\Entity\MenuLink;

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

    // @codingStandardsIgnoreStart

    /**
     * Get single node with active translations and author
     *
     * @param int $id
     *
     * @return MenuLink
     */
    public function getById($id)
    {
        $qb = $this->newORMQuery()
            ->select('m', 't')
            ->from($this->getClassName(), 'm')
            ->leftJoin('m.translations', 't', 'WITH', 't.isActive = 1')
            ->where('m.id = :id')
            ->setParameter('id', $id);
        return $qb->getQuery()->getSingleResult();
    }

    /*
    |--------------------------------------------------------------------------
    | START TreeRepository
    |--------------------------------------------------------------------------
    */

    /**
     * Get all ancestors nodes to specific node
     *
     * @param TreeNode $node
     * @param int      $hydrate
     *
     * @return mixed
     */
    public function getAncestors(TreeNode $node, $hydrate = Query::HYDRATE_ARRAY)
    {
        if ($node->getPath() != '/') { // root does not have ancestors
            $ancestorsIds = $node->getAncestorsIds(); //

            $qb = $this->newORMQuery()
                ->select('n')
                ->from($this->getClassName(), 'n')
                ->where('n.id IN(:ids)')
                ->setParameter('ids', $ancestorsIds)
                ->orderBy('n.level');

            $nodes = $qb->getQuery()->getResult($hydrate);
            return $nodes;
        }
        return [];
    }

    /**
     * Get all descendants nodes to specific node
     *
     * @param TreeNode $node
     * @param bool     $tree If you want get in tree structure instead of list
     * @param int      $hydrate
     *
     * @return mixed
     */
    public function getDescendants(TreeNode $node, $tree = false, $hydrate = Query::HYDRATE_ARRAY)
    {
        $qb = $this->newORMQuery()
            ->from($this->getClassName(), 'n')
            ->where('n.path LIKE :path')
            ->setParameter('path', $node->getChildrenPath() . '%')
            ->orderBy('n.level');
        if ($tree) {
            $qb->select('n', 'c')
                ->leftJoin(
                    'n.children',
                    'c',
                    'WITH',
                    'c.level > :nodeLevel'
                )
                ->setParameter('nodeLevel', $node->getLevel());
        } else {
            $qb->select('n');
        }
        $nodes = $qb->getQuery()->getResult($hydrate);
        if ($tree) {
            return array_filter(
                $nodes,
                function ($item) use ($node) { // We return children's array because we don't have one root
                    $level = (is_array($item)) ? @$item['level'] : $item->getLevel(); // @TODO Ugly HAX ['level']
                    return ($level == $node->getLevel() + 1);
                }
            );
        } else {
            return $nodes;
        }
    }

    /**
     * Get all children nodes to specific node
     *
     * @param TreeNode $node
     * @param array    $criteria
     * @param array    $orderBy
     * @param null     $limit
     * @param null     $offset
     *
     * @return mixed
     */
    public function getChildren(TreeNode $node, array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        return parent::findBy(array_merge($criteria, ['path' => $node->getChildrenPath()]), $orderBy, $limit, $offset);
    }

    /**
     * Get all siblings nodes to specific node
     *
     * @param TreeNode $node
     * @param array    $criteria
     * @param array    $orderBy
     * @param null     $limit
     * @param null     $offset
     *
     * @return mixed
     */
    public function getSiblings(TreeNode $node, array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    {
        $siblings = parent::findBy(array_merge($criteria, ['path' => $node->getPath()]), $orderBy, $limit, $offset);
        // we skip $node
        return array_filter(
            $siblings,
            function ($var) use ($node) {
                return $var->getId() != $node->getId();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | END TreeRepository
    |--------------------------------------------------------------------------
    */

    public function create(MenuLink $content)
    {
        $this->_em->persist($content);
    }

    public function update(MenuLink $content)
    {
        $this->_em->persist($content);
    }

    public function delete(MenuLink $content)
    {
        $this->_em->remove($content);
    }

    public function commit()
    {
        $this->_em->flush();
    }

    // @codingStandardsIgnoreEnd
}
