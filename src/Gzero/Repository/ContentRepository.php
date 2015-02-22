<?php namespace Gzero\Repository;

use Gzero\EloquentTree\Model\Tree;
use Gzero\Entity\ContentTranslation;
use Gzero\Entity\ContentType;
use Gzero\Entity\Content as C;
use Gzero\Entity\Route;
use Gzero\Entity\RouteTranslation;
use Gzero\Entity\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Events\Dispatcher;

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
     * @var C
     */
    protected $model;

    /**
     * The events dispatcher
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * Content repository constructor
     *
     * @param C          $content Content model
     * @param Dispatcher $events  Events dispatcher
     */
    public function __construct(C $content, Dispatcher $events)
    {
        $this->model  = $content;
        $this->events = $events;
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
        return $this->newORMQuery()->getRelation('type')->getQuery()->find($name);
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
        return $this->newORMQuery()->getRelation('translations')->getQuery()->find($id);
    }

    /**
     * Get content entity by url address
     *
     * @param string $url      Url address
     * @param string $langCode Lang code
     *
     * @return C $content Content content
     * @throws RepositoryException
     */
    public function getByUrl($url, $langCode)
    {
        if (!empty($url) && !empty($langCode)) {
            $content = $this->newORMQuery()
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
                ->where('Routes.isActive', '=', 1)// We only need content with active route
                ->first(['Contents.*']);
            if ($content) {
                return $content;
            } else {
                throw new RepositoryException(
                    "Content with url: '" . $url . "' in language '" . $langCode . "' doesn't exist",
                    500
                );
            }
        } else {
            throw new RepositoryException("Url and Language code of translation is required", 500);
        }
    }

    /**
     * Get all translations to specific content
     *
     * @param C        $content  Content content
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getTranslations(C $content, array $criteria, array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query = $content->translations(false);
        $this->handleFilterCriteria($this->getTranslationsTableName(), $criteria, $query);
        $this->handleOrderBy(
            $this->getTranslationsTableName(),
            $orderBy,
            $query,
            function ($query) { // default order by
                $query->orderBy('ContentTranslations.isActive', 'DESC');
            }
        );
        return $this->handlePagination($this->getTranslationsTableName(), $query, $page, $pageSize);
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
     * Get all descendants nodes of specific node
     *
     * @param Tree     $node     Tree node
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getDescendants(Tree $node, array $criteria, array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query = $node->findDescendants();
        $this->handleTranslationsJoin($criteria, $orderBy, $query);
        $this->handleFilterCriteria($this->getTableName(), $criteria, $query);
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $orderBy,
            $query,
            $this->contentDefaultOrderBy()
        );
        return $this->handlePagination($this->getTableName(), $query, $page, $pageSize);
    }

    /**
     * Get all children nodes to specific node
     *
     * @param Tree     $node     Tree node
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getChildren(Tree $node, array $criteria, array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query = $node->children();
        $this->handleTranslationsJoin($criteria, $orderBy, $query);
        $this->handleFilterCriteria($this->getTableName(), $criteria, $query);
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $orderBy,
            $query,
            $this->contentDefaultOrderBy()
        );
        return $this->handlePagination($this->getTableName(), $query, $page, $pageSize);
    }

    /**
     * Get all contents with specific criteria
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getContents(array $criteria, array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query = $this->newORMQuery();
        $this->handleTranslationsJoin($criteria, $orderBy, $query);
        $this->handleFilterCriteria($this->getTableName(), $criteria, $query);
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $orderBy,
            $query,
            $this->contentDefaultOrderBy()
        );
        return $this->handlePagination($this->getTableName(), $query, $page, $pageSize);
    }

    /**
     * Get all contents for specified root node with specific criteria as nested tree
     *
     * @param Tree  $root     Root node
     * @param array $criteria Filter criteria
     * @param array $orderBy  Array of columns
     *
     * @return EloquentCollection
     */
    public function getTree(Tree $root, array $criteria, array $orderBy = [])
    {
        return $root->buildTree($this->getDescendants($root, $criteria, $orderBy, null));
    }

    /**
     * Build tree from specified nodes
     *
     * @param Collection $nodes Tree nodes
     *
     * @return EloquentCollection
     */
    public function buildTree($nodes)
    {
        return $this->model->buildTree($nodes);
    }

    /**
     * Get all root contents
     *
     * @param array $criteria Filter criteria
     * @param array $orderBy  Array of columns
     *
     * @return mixed
     * @throws RepositoryException
     */
    public function getRoots(array $criteria, array $orderBy = [])
    {
        $query = $this->getEloquentModel()->getRoots();
        $this->handleTranslationsJoin($criteria, $orderBy, $query);
        $this->handleFilterCriteria($this->getTableName(), $criteria, $query);
        $this->handleAuthorJoin($query);
        return $this->handlePagination($this->getTableName(), $query, null, null);
    }

    /**
     * Get translation of specified content by id.
     *
     * @param C   $content Content entity
     * @param int $id      Content Translation id
     *
     * @return ContentTranslation
     */
    public function getContentTranslationById(C $content, $id)
    {
        return $content->translations(false)->where('id', '=', $id)->first();
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
     * @return C
     */
    public function create(Array $data, User $author = null)
    {
        $content = $this->newQuery()->transaction(
            function () use ($data, $author) {
                $translations = array_get($data, 'translations'); // Nested relation fields
                if (!empty($translations) && array_key_exists('type', $data)) {
                    // Check if type exist
                    $this->validateType($data['type']);
                    // Content
                    $content = new C();
                    $content->fill($data);
                    if ($author) {
                        $content->author()->associate($author);
                    }
                    if (!empty($data['parentId'])) {
                        $parent = $this->getById($data['parentId']);
                        if (!empty($parent)) {
                            $content->setChildOf($parent);
                        } else {
                            throw new RepositoryException('Parent node id: ' . $data['parentId'] . ' doesn\'t exist');
                        }
                    } else {
                        $content->setAsRoot();
                    }
                    // Content translations
                    $this->createTranslation($content, $translations);
                    return $content;
                } else {
                    throw new RepositoryException("Content type and translation is required", 500);
                }
            }
        );
        $this->events->fire('content.created', [$content]);
        return $content;
    }

    /**
     * Creates translation for specified content entity it also handles route creation
     *
     * @param C     $content Content entity
     * @param array $data    new data to save
     *
     * @return ContentTranslation
     * @throws RepositoryException
     */
    public function createTranslation(C $content, Array $data)
    {
        if (array_key_exists('langCode', $data) && array_key_exists('title', $data)) {
            $translation = $this->newQuery()->transaction(
                function () use ($content, $data) {
                    // Set all translation of this content as inactive
                    $this->disableActiveTranslations($content->id, $data['langCode']);
                    $translation = new ContentTranslation();
                    $translation->fill($data);
                    $translation->isActive = 1; // Because only recent translation is active
                    $content->translations()->save($translation);
                    return $translation;
                }
            );
            // Creating or updating route from translations
            $this->createRoute($content, $data);
            $this->events->fire('content.translation.created', [$content, $translation]);
            return $translation;
        } else {
            throw new RepositoryException("Language code and title of translation is required", 500);
        }
    }


    /**
     * Function handles creation of route from translations
     *
     * @param C     $content      Content entity
     * @param array $translations translations data to save
     *
     * @return Route
     * @throws RepositoryException
     */
    public function createRoute(C $content, $translations)
    {
        if (array_key_exists('langCode', $translations) && array_key_exists('title', $translations)) {
            $route = $this->newQuery()->transaction(
                function () use ($content, $translations) {
                    $url = '';
                    // Search for parent, to get its url
                    if (!empty($content->parentId)) {
                        $parent = $this->getById($content->parentId);
                        if (!empty($parent)) {
                            $url = $parent->getUrl($translations['langCode']) . '/';
                        }
                    }
                    // Search for route, or instantiate a new instance
                    $route = Route::firstOrNew(['routableId' => $content->id, 'isActive' => 1]);
                    $content->route()->save($route);
                    //  Search for route translations, or instantiate a new instance
                    $routeTranslation      = RouteTranslation::firstOrNew(
                        ['routeId' => $route->id, 'langCode' => $translations['langCode'], 'isActive' => 1]
                    );
                    $routeTranslation->url = $this->buildUniqueUrl(
                        $url . str_slug($translations['title']),
                        $translations['langCode']
                    );
                    $route->translations()->save($routeTranslation);
                    return $route;
                }
            );
            $this->events->fire('route.created', [$route]);
            return $route;
        } else {
            throw new RepositoryException("Language code and title of translation is required", 500);
        }
    }

    /**
     * Update specific content entity
     *
     * @param C         $content  Content entity
     * @param array     $data     new data to save
     * @param User|null $modifier User entity
     *
     * @return C
     * @SuppressWarnings("unused")
     */
    public function update(C $content, Array $data, User $modifier = null)
    {
        $content = $this->newQuery()->transaction(
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
        $this->events->fire('content.updated', [$content]);
        return $content;
    }

    /**
     * Delete specific content entity
     *
     * @param C $content Content entity to delete
     *
     * @return boolean
     */
    public function delete(C $content)
    {
        return $this->newQuery()->transaction(
            function () use ($content) {
                $routeRelation  = $content->route();
                $descendantsIds = $content->findDescendants()->lists('id');
                // First we need to delete all routes because it's polymorphic relation
                $this->newQuery()
                    ->table($routeRelation->getModel()->getTable())
                    ->where($routeRelation->getPlainMorphType(), '=', get_class($content))
                    ->whereIn($routeRelation->getPlainForeignKey(), $descendantsIds)
                    ->delete();
                return $content->delete();
            }
        );
    }

    /**
     * Delete specific content translation entity
     *
     * @param ContentTranslation $translation entity to delete
     *
     * @return boolean
     */
    public function deleteTranslation(ContentTranslation $translation)
    {
        return $this->newQuery()->transaction(
            function () use ($translation) {
                return $translation->delete();
            }
        );
    }

    /**
     * Default orderBy for content query
     *
     * @return callable
     */
    private function contentDefaultOrderBy()
    {
        return function ($query) {
            $query->orderBy('Contents.weight', 'ASC');
            $query->orderBy('Contents.createdAt', 'DESC');
        };
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
    private function handleTranslationsJoin(array &$criteria, array $orderBy, $query)
    {
        if (!empty($criteria['lang'])) {
            $query->leftJoin(
                'ContentTranslations',
                function ($join) use ($criteria) {
                    $join->on('Contents.id', '=', 'ContentTranslations.contentId')
                        ->where('ContentTranslations.langCode', '=', $criteria['lang']['value']);
                }
            );
            unset($criteria['lang']);
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
     * @param EloquentCollection $results Eloquent collection
     *
     * @return void
     */
    protected function listEagerLoad($results)
    {
        $results->load('route.translations', 'translations', 'author');
    }


    /**
     * Function returns an unique url address from given url in specific language
     *
     * @param string $url      string url address to search for
     * @param string $langCode translation language
     *
     * @return string $url an unique url address
     */
    private function buildUniqueUrl($url, $langCode)
    {
        // search for duplicated url
        $count = $this->newQuery()
            ->table('RouteTranslations')
            ->where('langCode', $langCode)
            ->whereRaw("url REGEXP '^$url($|-[0-9]+$)'")
            ->count();
        return ($count) ? $url . '-' . $count : $url;
    }

    /**
     * Checks if provided type exists
     *
     * @param string $type type name
     *
     * @throws RepositoryException
     * @return string
     */
    private function validateType($type)
    {
        // TODO get registered types
        $types = ['content', 'category'];
        if (in_array($type, $types)) {
            return $type;
        } else {
            throw new RepositoryException("Content type '" . $type . "' doesn't exist", 500);
        }

    }

}
