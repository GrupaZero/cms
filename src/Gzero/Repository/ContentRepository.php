<?php namespace Gzero\Repository;

use Gzero\Entity\ContentTranslation;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Gzero\Model\Content;
use Illuminate\Pagination\Paginator;

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
class ContentRepository {

    /**
     * @var Content
     */
    private $queryBuilder;

    /**
     * Content repository constructor
     *
     * @param Content $content Content model
     */
    public function __construct(Content $content)
    {
        $this->queryBuilder = $content;
    }

    /**
     * Get single content with active translations and author.
     *
     * @param integer $id Test.
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
     * Get content type by id.
     *
     * @param int $id Content id
     *
     * @return ContentType
     */
    public function getTypeById($id)
    {
        return $this->getEntityManager()->find($this->getTypeClassName(), $id);
    }

    /**
     * Get content translation by id.
     *
     * @param int $id Content Translation id
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
     * Get content entity by url address
     *
     * @param string $url           Url address
     * @param Lang   $lang          Lang entity
     * @param bool   $isActiveCheck By default, we search only with active translation
     *
     * @return array
     */
    public function getByUrl($url, Lang $lang, $isActiveCheck = true)
    {
        $qb = $this->newQB();
        // isActive condition
        if ($isActiveCheck) {
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
     * @param TreeNode $node    Node to find ancestors
     * @param int      $hydrate Doctrine2 hydrate mode. Default - Query::HYDRATE_ARRAY
     *
     * @return mixed
     */
    public function getAncestors(TreeNode $node, $hydrate = Query::HYDRATE_ARRAY)
    {
        // root does not have ancestors
        if ($node->getPath() != '/') {
            $ancestorsIds = $node->getAncestorsIds();
            $qb           = $this->newQB()
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
     * @param TreeNode $node    Node to find descendants
     * @param bool     $tree    If you want get in tree structure instead of list
     * @param int      $hydrate Doctrine2 hydrate mode. Default - Query::HYDRATE_ARRAY
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
                    // @TODO Ugly HAX ['level']
                    $level = (is_array($item)) ? @$item['level'] : $item->getLevel();
                    return ($level == $node->getLevel() + 1);
                }
            );
        } else {
            return $nodes;
        }
    }

    /**
     * Get all children nodes to specific node.
     *
     * @param TreeNode $node     Node to find children
     * @param array    $criteria Array of conditions
     * @param array    $orderBy  Array of columns
     * @param int|null $limit    Limit results
     * @param int|null $offset   Start from
     *
     * @return mixed
     * @SuppressWarnings("unused") We'll refactor whole repository
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
     * @param TreeNode $node     Node to find siblings
     * @param array    $criteria Array of conditions
     * @param array    $orderBy  Array of columns
     * @param int|null $limit    Limit results
     * @param int|null $offset   Start from
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
     * @param Lang     $lang     Lang entity
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number
     * @param int|null $pageSize Limit results
     *
     * @return Collection
     */
    public function getRootContentsDoctrine(Lang $lang, array $criteria, array $orderBy = [], $page = null, $pageSize = null)
    {
        $query = $this->newQB();
        $query->select('c,ct,a,r,rt')
            ->from($this->getClassName(), 'c')
            ->innerJoin('c.route', 'r')
            ->leftJoin('c.translations', 'ct', 'WITH', 'ct.isActive = 1 and ct.langCode = :langCode')
            ->leftJoin('r.translations', 'rt', 'WITH', 'rt.langCode = :langCode')
            ->leftJoin('c.author', 'a')
            ->where(
                $query->expr()->andX(
                    $query->expr()->eq('c.level', ':level'),
                    $this->handleFilterCriteria('c', $criteria, $query)
                )
            )
            ->setParameters(
                [
                    'level'    => 0,
                    'langCode' => $lang->getCode()
                ]
            );

        $this->handleOrderBy('c', $orderBy, $query);

        $paginator = new Paginator($query);
        $paginator
            ->getQuery()
            ->setFirstResult($pageSize * ($page - 1))// set the offset
            ->setMaxResults($pageSize); // set the limit

        return new Collection(
            $paginator->getQuery()->getArrayResult(),
            $paginator->count()
        );
    }

    /**
     * Eloquent version
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getRootContents(array $criteria, array $orderBy = [], $page = 1, $pageSize = 20)
    {
        $this->validateCriteria($criteria);
        $query = $this->queryBuilder->newQuery()
            ->leftJoin(
                'ContentTranslations',
                function ($join) use ($criteria) {
                    $join->on('Contents.id', '=', 'ContentTranslations.contentId')
                        ->where('ContentTranslations.langCode', '=', $criteria['lang']);
                }
            );
        $count = clone $query;
        $this->handleOrderBy('Contents', $orderBy, $query);
        $this->handleFilterCriteria('Contents', $criteria, $query);
        $results = $query
            ->offset($pageSize * ($page - 1))
            ->limit($pageSize)
            ->get(['Contents.*']);
        $results->load('route.translations', 'translations');
        return \Paginator::make($results->all(), $count->select('Contents.id')->count(), $pageSize);
    }

    /*
    |--------------------------------------------------------------------------
    | END TreeRepository
    |--------------------------------------------------------------------------
    */

    /**
     * Create specific content entity
     *
     * @param Content $content Content entity to persist
     * @param bool    $sync    Auto commit
     *
     * @return void
     */
    public function add(Content $content, $sync = false)
    {
        $this->getEntityManager()->persist($content);
        if ($sync) {
            $this->commit();
        }
    }

    /**
     * Delete specific content entity
     *
     * @param Content $content Content entity to delete
     * @param bool    $sync    Auto commit
     *
     * @return void
     */
    public function remove(Content $content, $sync = false)
    {
        $this->getEntityManager()->remove($content);
        if ($sync) {
            $this->commit();
        }
    }

    /**
     * Add filter rules to query
     *
     * @param string $entityAlias Entity alias to where clause
     * @param array  $criteria    Array with filer criteria
     * @param mixed  $query       Query to add filter rules
     *
     * @return void
     */
    private function handleFilterCriteria($entityAlias, array $criteria, $query)
    {
        $conditions = [];
        if (isset($criteria['lang'])) { // Simple hax for now
            unset($criteria['lang']);
        }
        foreach ($criteria as $condition => $value) {
            $conditions[] = $query->where($entityAlias . '.' . $condition, '=', $value);
        }
    }

    /**
     * Add sorting rules to query
     *
     * @param string $entityAlias Entity alias to orderBy clause
     * @param array  $orderBy     Array with sort columns and directions
     * @param mixed  $query       Query to add sorting rules
     *
     * @return void
     */
    private function handleOrderBy($entityAlias, array $orderBy, $query)
    {
        foreach ($orderBy as $sort => $order) {
            $query->orderBy($entityAlias . '.' . $sort, $order);
        }
    }

    /**
     * Check if all required criteria are set
     *
     * @param array $criteria Array with criteria
     *
     * @return void
     * @throws RepositoryException
     */
    private function validateCriteria(array $criteria)
    {
        if (!isset($criteria['lang'])) {
            throw new RepositoryException('Repository Validation Error: \'lang\' criteria is required');
        }
    }
}
