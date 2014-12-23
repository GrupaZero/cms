<?php namespace Gzero\Repository;

use Gzero\EloquentTree\Model\Tree;
use Gzero\Entity\ContentTranslation;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Gzero\Entity\Content;
use Gzero\Entity\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Config\Definition\Exception\Exception;

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
class ContentRepository extends BaseRepository {

    /**
     * @var Content
     */
    protected $model;

    /**
     * Content repository constructor
     *
     * @param Content $content Content model
     */
    public function __construct(Content $content)
    {
        $this->model = $content;
    }

    /**
     * Get single content
     *
     * @param integer $id Content id
     *
     * @return Content
     */
    public function getById($id)
    {
        return $this->newQB()->find($id);
    }

    /**
     * Get content type by id.
     *
     * @param int $name Content id
     *
     * @return ContentType
     */
    public function getTypeByName($name)
    {
        return $this->newQB()->getRelation('type')->getQuery()->find($name);
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
        return $this->newQB()->getRelation('translations')->getQuery()->find($id);
    }

    /**
     * Get content entity by url address
     *
     * @param string $url      Url address
     * @param string $langCode Lang code
     *
     * @return Content
     */
    public function getByUrl($url, $langCode)
    {
        return $this->newQB()
            ->join(
                'Routes',
                function ($join) {
                    $join->on('Contents.id', '=', 'Routes.routableId')
                        ->where('Routes.RoutableType', '=', 'Gzero\Entity\Content');
                }
            )
            ->join(
                'RouteTranslations',
                function ($join) use ($langCode) {
                    $join->on('Routes.id', '=', 'RouteTranslations.routeId')
                        ->where('RouteTranslations.langCode', '=', $langCode);
                }
            )->where('RouteTranslations.url', '=', $url)
            ->first(['Contents.*']);
    }

    /*
    |--------------------------------------------------------------------------
    | START TreeRepository
    |--------------------------------------------------------------------------
    */

    ///**
    // * Get all ancestors nodes to specific node
    // *
    // * @param TreeNode $node    Node to find ancestors
    // * @param int      $hydrate Doctrine2 hydrate mode. Default - Query::HYDRATE_ARRAY
    // *
    // * @return mixed
    // */
    //public function getAncestors(TreeNode $node, $hydrate = Query::HYDRATE_ARRAY)
    //{
    //    // root does not have ancestors
    //    if ($node->getPath() != '/') {
    //        $ancestorsIds = $node->getAncestorsIds();
    //        $qb           = $this->newQB()
    //            ->select('n')
    //            ->from($this->getClassName(), 'n')
    //            ->where('n.id IN(:ids)')
    //            ->setParameter('ids', $ancestorsIds)
    //            ->orderBy('n.level');
    //
    //        $nodes = $qb->getQuery()->getResult($hydrate);
    //        return $nodes;
    //    }
    //    return [];
    //}

    ///**
    // * Get all descendants nodes to specific node
    // *
    // * @param TreeNode $node    Node to find descendants
    // * @param bool     $tree    If you want get in tree structure instead of list
    // * @param int      $hydrate Doctrine2 hydrate mode. Default - Query::HYDRATE_ARRAY
    // *
    // * @return mixed
    // */
    //public function getDescendants(TreeNode $node, $tree = false, $hydrate = Query::HYDRATE_ARRAY)
    //{
    //    $qb = $this->newQB()
    //        ->from($this->getClassName(), 'n')
    //        ->where('n.path LIKE :path')
    //        ->setParameter('path', $node->getChildrenPath() . '%')
    //        ->orderBy('n.level');
    //    if ($tree) {
    //        $qb->select('n', 'c')
    //            ->leftJoin(
    //                'n.children',
    //                'c',
    //                'WITH',
    //                'c.level > :nodeLevel'
    //            )
    //            ->setParameter('nodeLevel', $node->getLevel());
    //    } else {
    //        $qb->select('n');
    //    }
    //    $nodes = $qb->getQuery()->getResult($hydrate);
    //    if ($tree) {
    //        return array_filter(
    //            $nodes,
    //            function ($item) use ($node) { // We return children's array because we don't have one root
    //                // @TODO Ugly HAX ['level']
    //                $level = (is_array($item)) ? @$item['level'] : $item->getLevel();
    //                return ($level == $node->getLevel() + 1);
    //            }
    //        );
    //    } else {
    //        return $nodes;
    //    }
    //}

    ///**
    // *
    // *
    // * @param TreeNode $node     Node to find children
    // * @param array    $criteria Array of conditions
    // * @param array    $orderBy  Array of columns
    // * @param int|null $limit    Limit results
    // * @param int|null $offset   Start from
    // *
    // * @return mixed
    // * @SuppressWarnings("unused") We'll refactor whole repository
    // */
    //public function getChildren(TreeNode $node, array $criteria = [], array $orderBy = [], $limit = null, $offset = null)
    //{
    //    $qb = $this->newQB()
    //        ->select('c,t,a')
    //        ->from($this->getClassName(), 'c')
    //        ->leftJoin('c.translations', 't', 'WITH', 't.isActive = 1')
    //        ->leftJoin('c.author', 'a')
    //        ->where('c.path = :path')
    //        ->setParameter('path', $node->getChildrenPath())
    //        ->setFirstResult($offset)
    //        ->setMaxResults($limit);
    //
    //    foreach ($orderBy as $sort => $order) {
    //        $qb->orderBy($sort, $order);
    //    }
    //    return $qb->getQuery()->getArrayResult();
    //}

    /**
     * Get all descendants nodes to specific node
     *
     * @param Tree  $node     Tree node
     * @param array $criteria Filter criteria
     * @param array $orderBy  Array of columns
     * @param bool  $tree     If you want get in tree structure instead of list
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getDescendants(Tree $node, array $criteria, array $orderBy = [], $tree = false)
    {
        $query = $node->findDescendants();
        $this->handleTranslationsJoin($criteria, $orderBy, $query);
        $this->handleFilterCriteria($criteria, $query);
        $this->handleOrderBy($orderBy, $query);
        $results = $query->get(['Contents.*']);
        $this->listEagerLoad($results);
        if ($tree) {
            return $node->buildTree($results);
        }
        return $results;
    }

    /**
     * Get all children nodes to specific node
     *
     * @param Tree     $node     Tree node
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getChildren(Tree $node, array $criteria, array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query = $node->children();
        $this->handleTranslationsJoin($criteria, $orderBy, $query);
        $this->handleFilterCriteria($criteria, $query);
        $this->handleAuthorJoin($query);
        $count = clone $query;
        $this->handleOrderBy($orderBy, $query);
        $results = $query
            ->offset($pageSize * ($page - 1))
            ->limit($pageSize)
            ->get(['Contents.*']);
        $this->listEagerLoad($results);
        \Paginator::setCurrentPage($page); // We need to set current page because laravel use input to set this property
        return \Paginator::make($results->all(), $count->select('Contents.id')->count(), $pageSize);
    }

    ///**
    // * Get all siblings nodes to specific node
    // *
    // * @param TreeNode $node     Node to find siblings
    // * @param array    $criteria Array of conditions
    // * @param array    $orderBy  Array of columns
    // * @param int|null $limit    Limit results
    // * @param int|null $offset   Start from
    // *
    // * @return mixed
    // */
    //public function getSiblings(TreeNode $node, array $criteria = [], array $orderBy = null, $limit = null, $offset = null)
    //{
    //    $siblings = parent::findBy(array_merge($criteria, ['path' => $node->getPath()]), $orderBy, $limit, $offset);
    //    // we skip $node
    //    return array_filter(
    //        $siblings,
    //        function ($var) use ($node) {
    //            return $var->getId() != $node->getId();
    //        }
    //    );
    //}

    /**
     * Get all contents with specific criteria
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getContents(array $criteria, array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query = $this->newQB();
        $this->handleTranslationsJoin($criteria, $orderBy, $query);
        $this->handleFilterCriteria($criteria, $query);
        $this->handleAuthorJoin($query);
        $count = clone $query;
        $this->handleOrderBy($orderBy, $query);
        $results = $query
            ->offset($pageSize * ($page - 1))
            ->limit($pageSize)
            ->get(['Contents.*']);
        $this->listEagerLoad($results);
        \Paginator::setCurrentPage($page); // We need to set current page because laravel use input to set this property
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
     * @param array     $data   Content entity to persist
     * @param User|null $author Author entity
     *
     * @return Content
     */
    public function create(Array $data, User $author = null)
    {
        return DB::transaction(
            function () use ($data, $author) {
                $content = new Content();
                $content->fill($data);
                $content->author()->associate($author);
                $translation = new ContentTranslation();
                $translation->fill($this->getNestedFields('translations', $data));
                $translation->isActive = 1; // Because this is first translation
                $content->save();
                $content->translations()->save($translation);
                return $content;
            }
        );
    }

    /**
     * Update specific content entity
     *
     * @param Content   $content  Content entity
     * @param array     $data     new data to save
     * @param User|null $modifier User entity
     *
     * @return Content
     * @SuppressWarnings("unused")
     */
    public function update(Content $content, Array $data, User $modifier = null)
    {
        return DB::transaction(
            function () use ($content, $data, $modifier) {
                $content->fill($data);
                if (!empty($data['parentId'])) {
                    $parent = $this->getById($data['parentId']);
                    if (!empty($parent)) {
                        $content->setChildOf($parent);
                    } else {
                        $content->setAsRoot();
                    }
                } else {
                    $content->save();
                }
                return $content;
            }
        );
    }

    ///**
    // * Delete specific content entity
    // *
    // * @param Content $content Content entity to delete
    // * @param bool    $sync    Auto commit
    // *
    // * @return void
    // */
    //public function remove(Content $content, $sync = false)
    //{
    //    $this->getEntityManager()->remove($content);
    //    if ($sync) {
    //        $this->commit();
    //    }
    //}

    /**
     * Add filter rules to query
     *
     * @param array $criteria Array with filer criteria
     * @param mixed $query    Query to add filter rules
     *
     * @return void
     */
    private function handleFilterCriteria(array $criteria, $query)
    {
        $conditions = [];
        if (isset($criteria['lang'])) { // Simple hax for now
            unset($criteria['lang']);
        }
        foreach ($criteria as $condition => $value) {
            $conditions[] = $query->where(
                $this->resolveTableName('Contents', $value['relation'], $query) . $condition,
                '=',
                $value['value']
            );
        }
    }

    /**
     * Add sorting rules to query
     *
     * @param array $orderBy Array with sort columns and directions
     * @param mixed $query   Query to add sorting rules
     *
     * @return void
     */
    private function handleOrderBy(array $orderBy, $query)
    {
        if (empty($orderBy)) { // Default order
            $query->orderBy('Contents.weight', 'ASC');
            $query->orderBy('Contents.createdAt', 'DESC');
        }
        foreach ($orderBy as $sort => $order) {
            $query->orderBy($this->resolveTableName('Contents', $order['relation'], $query) . $sort, $order['direction']);
        }
    }

    /**
     * Handle joining content translations table based on provided criteria
     *
     * @param array $criteria Array with filter criteria
     * @param array $orderBy  Array with orderBy
     * @param mixed $query    Eloquent query object
     *
     * @throws RepositoryException
     * @return array
     */
    private function handleTranslationsJoin(array $criteria, array $orderBy, $query)
    {
        if (!empty($criteria['lang'])) {
            $query->leftJoin(
                'ContentTranslations',
                function ($join) use ($criteria) {
                    $join->on('Contents.id', '=', 'ContentTranslations.contentId')
                        ->where('ContentTranslations.langCode', '=', $criteria['lang']['value']);
                }
            );
        } else {
            if (!empty($orderBy)) {
                throw new RepositoryException('Repository Validation Error: \'lang\' criteria is required', 500);
            }
        }

    }

    /**
     * Handle joining users table
     *
     * @param mixed $query Eloquent query object
     *
     * @return array
     */
    private function handleAuthorJoin($query)
    {
        $query->leftJoin(
            'Users',
            function ($join) {
                $join->on('Contents.authorId', '=', 'Users.id');
            }
        );
    }

    /**
     * Eager load relations for eloquent collection
     *
     * @param Collection $results Eloquent collection
     *
     * @return void
     */
    private function listEagerLoad($results)
    {
        $results->load('route.translations', 'translations');
    }
}
