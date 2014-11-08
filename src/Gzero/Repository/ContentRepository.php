<?php namespace Gzero\Repository;

use Doctrine\ORM\Query;
use Gzero\Doctrine2Extensions\Tree\TreeNode;
use Gzero\Doctrine2Extensions\Tree\TreeRepository;
use Gzero\Entity\Content;
use Gzero\Entity\ContentTranslation;
use Gzero\Entity\ContentType;
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

    /**
     * Get single content with active translations and author
     *
     * @param int $id
     *
     * @return Content
     */
    public function getById($id)
    {
        $qb = $this->newQB()
            ->select('c,t,a')
            ->from($this->getClassName(), 'c')
            ->leftJoin('c.translations', 't', 'WITH', 't.isActive = 1')
            ->leftJoin('c.author', 'a')
            ->where('c.id = :id')
            ->setParameter('id', $id);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get content type by id
     *
     * @param $id
     *
     * @return ContentType
     */
    public function getTypeById($id)
    {
        return $this->_em->find($this->getTypeClassName(), $id);
    }

    /**
     * Get content translation by id
     *
     * @param $id
     *
     * @return ContentTranslation
     */
    public function getTranslationById($id)
    {
        $qb = $this->newQB()
            ->select('t')
            ->from($this->getTranslationClassName(), 't')
            ->where('t.id = :id')
            ->orderBy('t.isActive')
            ->setParameter('id', $id);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $url
     * @param Lang   $lang
     * @param bool   $isActiveCheck By default, we search only with active translation
     *
     * @return array
     */
    public function getByUrl($url, Lang $lang, $isActiveCheck = true)
    {
        $qb = $this->newQB();
        if ($isActiveCheck) { // isActive condition
            $condition = $qb->expr()->andx(
                $qb->expr()->eq('t.lang', ':lang'),
                $qb->expr()->eq('t.isActive', '1')
            );
        } else {
            $condition = 't.lang = :lang';
        }

        $qb->select('c')
            ->from($this->getClassName(), 'c')
            ->leftJoin(
                'c.translations',
                't',
                'WITH',
                $condition
            )
            ->where('t.url = :url')
            ->andWhere('c.isActive = 1')
            ->orderBy('c.weight')
            ->setParameter('lang', $lang->getCode())
            ->setParameter('url', $url);
        $result = $qb->getQuery()->getResult();
        return (!empty($result)) ? $result[0] : $result;
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

            $qb = $this->newQB()
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
        $qb = $this->newQB()
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
    public function getChildren(TreeNode $node, array $criteria = [], array $orderBy = [], $limit = null, $offset = null)
    {
        $qb = $this->newQB()
            ->select('c,t,a')
            ->from($this->getClassName(), 'c')
            ->leftJoin('c.translations', 't', 'WITH', 't.isActive = 1')
            ->leftJoin('c.author', 'a')
            ->where('c.path = :path')
            ->setParameter('path', $node->getChildrenPath())
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        foreach ($orderBy as $sort => $order) {
            $qb->orderBy($sort, $order);
        }
        return $qb->getQuery()->getArrayResult();
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

    /**
     * Get active contents of all type in level 0
     *
     * @param null  $limit
     * @param null  $offset
     * @param array $orderBy
     *
     * @return array
     */
    public function getRootContents(array $orderBy = [], $limit = null, $offset = null)
    {
        $qb = $this->newQB()
            ->select('c,t,a')
            ->from($this->getClassName(), 'c')
            ->leftJoin('c.translations', 't', 'WITH', 't.isActive = 1')
            ->leftJoin('c.author', 'a')
            ->where('c.level = :level')
            ->setParameter('level', 0)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        foreach ($orderBy as $sort => $order) {
            $qb->orderBy($sort, $order);
        }
        return $qb->getQuery()->getArrayResult();
    }

    /*
    |--------------------------------------------------------------------------
    | END TreeRepository
    |--------------------------------------------------------------------------
    */

    public function create(Content $content)
    {
        $this->_em->persist($content);
    }

    public function update(Content $content, array $data)
    {
        $translation = $content->getTranslations()->first();
        $translation->fill($data);
        $this->_em->persist($content);
    }

    public function delete(Content $content)
    {
        $this->_em->remove($content);
    }

    public function commit()
    {
        $this->_em->flush();
    }
}
