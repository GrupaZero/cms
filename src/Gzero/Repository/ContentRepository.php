<?php namespace Gzero\Repository;

use Gzero\Core\Exception;
use Gzero\EloquentTree\Model\Tree;
use Gzero\Entity\ContentTranslation;
use Gzero\Entity\ContentType;
use Gzero\Entity\Content;
use Gzero\Entity\Route;
use Gzero\Entity\RouteTranslation;
use Gzero\Entity\User;
use Illuminate\Database\Eloquent\Builder;
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
     * @var Content
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
     * @param Content    $content Content model
     * @param Dispatcher $events  Events dispatcher
     */
    public function __construct(Content $content, Dispatcher $events)
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
     * Get content route by id.
     *
     * @param int $id Content route id
     *
     * @return Route
     */
    public function getRouteById($id)
    {
        return $this->newORMQuery()->getRelation('route')->getQuery()->find($id);
    }

    /**
     * Get content entity by url address
     *
     * @param string $url      Url address
     * @param string $langCode Lang code
     *
     * @return Content $content Content content
     * @throws RepositoryException
     */
    public function getByUrl($url, $langCode)
    {
        return $this->newORMQuery()
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
    }

    /**
     * Get all translations to specific content
     *
     * @param Content  $content  Content content
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getTranslations(
        Content $content,
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $content->translations(false);
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleFilterCriteria($this->getTranslationsTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getTranslationsTableName(),
            $parsed['orderBy'],
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

    /**
     * Get all ancestors nodes to specific node
     *
     * @param Tree  $node     Tree node
     * @param array $criteria Array of conditions
     * @param array $orderBy  Array of columns
     *
     * @return EloquentCollection|static[]
     * @throws RepositoryException
     */
    public function getAncestors(Tree $node, array $criteria = [], array $orderBy = [])
    {
        $query  = $node->findAncestors();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        return $query->get();
    }

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
    public function getDescendants(
        Tree $node,
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $node->findDescendants();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
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
    public function getChildren(
        Tree $node,
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $node->children();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
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
    public function getContents(array $criteria = [], array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query  = $this->newORMQuery();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria(
            $this->getTableName(),
            $query,
            $parsed['filter']
        );
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->contentDefaultOrderBy()
        );
        return $this->handlePagination(
            $this->getTableName(),
            $query,
            $page,
            $pageSize
        );
    }

    /**
     * Get all contents with specific criteria sorted by level.
     * It can be used to build tree structure.
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getContentsByLevel(array $criteria = [], array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query  = $this->newORMTreeQuery();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria(
            $this->getTableName(),
            $query,
            $parsed['filter']
        );
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->contentDefaultOrderBy()
        );
        return $this->handlePagination(
            $this->getTableName(),
            $query,
            $page,
            $pageSize
        );
    }

    /**
     * Get all soft deleted contents with specific criteria with tree structure
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getDeletedContents(array $criteria = [], array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query  = $this->newORMQuery()->onlyTrashed();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria(
            $this->getTableName(),
            $query,
            $parsed['filter']
        );
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->contentDefaultOrderBy()
        );
        return $this->handlePagination(
            $this->getTableName(),
            $query,
            $page,
            $pageSize
        );
    }

    /**
     * Get all soft deleted contents with specific criteria
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getDeletedContentsByLevel(
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $this->newORMTreeQuery()->onlyTrashed();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria(
            $this->getTableName(),
            $query,
            $parsed['filter']
        );
        $this->handleAuthorJoin($query);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->contentDefaultOrderBy()
        );
        return $this->handlePagination(
            $this->getTableName(),
            $query,
            $page,
            $pageSize
        );
    }

    /**
     * Get all contents for specified root node with specific criteria as nested tree
     *
     * @param Tree  $root     Root node
     * @param array $criteria Filter criteria
     * @param array $orderBy  Array of columns
     * @param bool  $strict   If we want to make sure that there are no orphan nodes
     *
     * @return EloquentCollection
     */
    public function getTree(Tree $root, array $criteria = [], array $orderBy = [], $strict = true)
    {
        return $root->buildTree($this->getDescendants($root, $criteria, $orderBy, null), $strict);
    }

    /**
     * Build tree from specified nodes
     *
     * @param EloquentCollection $nodes  Tree nodes
     * @param bool               $strict If we want to make sure that there are no orphan nodes
     *
     * @return EloquentCollection
     */
    public function buildTree($nodes, $strict = true)
    {
        return $this->model->buildTree($nodes, $strict);
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
    public function getRoots(array $criteria = [], array $orderBy = [])
    {
        $query  = $this->getEloquentModel()->getRoots();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleAuthorJoin($query);
        return $this->handlePagination($this->getTableName(), $query, null, null);
    }

    /**
     * Get translation of specified content by id.
     *
     * @param Content $content Content entity
     * @param int     $id      Content Translation id
     *
     * @return ContentTranslation
     */
    public function getContentTranslationById(Content $content, $id)
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
     * @return Content
     */
    public function create(Array $data, User $author = null)
    {
        $content = $this->newQuery()->transaction(
            function () use ($data, $author) {
                $translations = array_get($data, 'translations'); // Nested relation fields
                if (empty($translations) || !array_key_exists('type', $data)) {
                    throw new RepositoryValidationException('Content type and translation is required');
                }
                // Check if type exist
                /** @TODO get registered types */
                $this->validateType($data['type'], ['content', 'category']);
                $content = new Content();
                $content->fill($data);
                $this->events->fire('content.creating', [$content, $author]);
                if ($author) {
                    $content->author()->associate($author);
                }
                if (!empty($data['parentId'])) {
                    $parent = $this->getById($data['parentId']);
                    if (empty($parent)) {
                        throw new RepositoryValidationException('Parent node id: ' . $data['parentId'] . ' doesn\'t exist');
                    }
                    // Check if parent is one of allowed type
                    /** @TODO get registered types */
                    $this->validateType(
                        $parent->type,
                        ['category'],
                        "Content type '" . $parent->type . "' is not allowed for the parent type"
                    );
                    $content->setChildOf($parent);
                } else {
                    $content->setAsRoot();
                }
                // Content translations
                $this->createTranslation($content, $translations);
                $content = $this->getById($content->id);
                $this->events->fire('content.created', [$content]);
                return $content;
            }
        );
        return $this->getById($content->id);
    }

    /**
     * Creates translation for specified content entity it also handles route creation
     *
     * @param Content $content Content entity
     * @param array   $data    new data to save
     *
     * @return ContentTranslation
     * @throws RepositoryValidationException
     */
    public function createTranslation(Content $content, Array $data)
    {
        if (!array_key_exists('langCode', $data) || !array_key_exists('title', $data)) {
            throw new RepositoryValidationException('Language code and title of translation is required');
        }
        // Create route only for the first translation
        if ($content->translations()->where('langCode', $data['langCode'])->first() === null) {
            $this->createRoute($content, $data['langCode'], $data['title']);
        };

        // New translation query
        $translation = $this->newQuery()->transaction(
            function () use ($content, $data) {
                // Set all translation of this content as inactive
                $this->disableActiveTranslations($content->id, $data['langCode']);
                $translation = new ContentTranslation();
                $translation->fill($data);
                $translation->isActive = 1; // Because only recent translation is active
                $this->events->fire('content.translation.creating', [$content, $translation]);
                $content->translations()->save($translation);
                $this->events->fire('content.translation.created', [$content, $translation]);
                return $translation;
            }
        );
        return $this->getTranslationById($translation->id);
    }

    /**
     * Function handles creation of route from translations
     *
     * @param Content $content   Content entity
     * @param string  $langCode  Lang code
     * @param string  $urlString string used to build unique url
     *
     * @return Route
     * @throws RepositoryValidationException
     */
    public function createRoute(Content $content, $langCode, $urlString)
    {
        $route = $this->newQuery()->transaction(
            function () use ($content, $langCode, $urlString) {
                $url = '';
                // Search for parent, to get its url
                if (!empty($content->parentId)) {
                    $parent = $this->getById($content->parentId);
                    if (!empty($parent)) {
                        try {
                            $url = $parent->getUrl($langCode) . '/';
                        } catch (Exception $e) {
                            throw new RepositoryValidationException(
                                "Parent has not been translated in this language, translate it first!"
                            );
                        }
                    }
                }
                // Search for route, or instantiate a new instance
                $route = Route::firstOrNew(['routableId' => $content->id, 'isActive' => 1]);
                $content->route()->save($route);
                //  Search for route translations, or instantiate a new instance
                $routeTranslation      = RouteTranslation::firstOrNew(
                    ['routeId' => $route->id, 'langCode' => $langCode, 'isActive' => 1]
                );
                $routeTranslation->url = $this->buildUniqueUrl(
                    $url . str_slug($urlString),
                    $langCode
                );
                $this->events->fire('route.creating', [$route]);
                $route->translations()->save($routeTranslation);
                $this->events->fire('route.created', [$route]);
                return $route;
            }
        );
        return $this->getRouteById($route->id);
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
        $content = $this->newQuery()->transaction(
            function () use ($content, $data, $modifier) {
                $content->fill($data);
                $this->events->fire('content.updating', [$content]);
                if (!empty($data['parentId'])) {
                    $this->handleParentUpdate($content, $data);
                } else {
                    $content->save();
                }
                $this->events->fire('content.updated', [$content]);
                return $content;
            }
        );
        return $content;
    }

    /**
     * Delete specific content entity using softDelete
     *
     * @param Content $content Content entity to delete
     *
     * @return boolean
     */
    public function delete(Content $content)
    {
        return $this->newQuery()->transaction(
            function () use ($content) {
                // When we're using softDelete, we need to manually softDeleted descendants rows
                foreach ($content->findDescendants()->get() as $node) {
                    $node->delete();
                }
                $this->events->fire('content.deleting', [$content]);
                $content->delete();
                $this->events->fire('content.deleted', [$content]);
                return true;
            }
        );
    }

    /**
     * Delete specific content entity using forceDelete
     *
     * @param Content $content Content entity to delete
     *
     * @return boolean
     */
    public function forceDelete(Content $content)
    {
        return $this->newQuery()->transaction(
            function () use ($content) {
                $routeRelation  = $content->route();
                $descendantsIds = $content->findDescendantsWithTrashed()->lists('id');
                // First we need to delete all routes because it's polymorphic relation
                $this->newQuery()
                    ->table($routeRelation->getModel()->getTable())
                    ->where($routeRelation->getPlainMorphType(), '=', get_class($content))
                    ->whereIn($routeRelation->getPlainForeignKey(), $descendantsIds)
                    ->delete();
                $this->events->fire('content.forceDeleting', [$content]);
                $this->getByIdWithTrashed($content->id)->forceDelete();
                $this->events->fire('content.forceDeleted', [$content]);
                return true;
            }
        );
    }

    /**
     * Delete specific content translation entity
     *
     * @param ContentTranslation $translation entity to delete
     *
     * @return bool
     * @throws RepositoryValidationException
     * @throws \Exception
     */
    public function deleteTranslation(ContentTranslation $translation)
    {
        if ($translation->isActive) {
            throw new RepositoryValidationException('Cannot delete active translation');
        }
        return $this->newQuery()->transaction(
            function () use ($translation) {
                return $translation->delete();
            }
        );
    }

    /**
     * Create new ORM tree query builder
     *
     * @return Builder
     */
    protected function newORMTreeQuery()
    {
        return parent::newORMQuery()->orderBy($this->model->getTreeColumn('level'), 'ASC');
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
     * @param array $parsedCriteria Array with filter criteria
     * @param array $parsedOrderBy  Array with orderBy
     * @param mixed $query          Eloquent query object
     *
     * @throws RepositoryValidationException
     * @return array
     */
    private function handleTranslationsJoin(array &$parsedCriteria, array $parsedOrderBy, $query)
    {
        if (!empty($parsedCriteria['lang'])) {
            $query->leftJoin(
                'ContentTranslations',
                function ($join) use ($parsedCriteria) {
                    $join->on('Contents.id', '=', 'ContentTranslations.contentId')
                        ->where('ContentTranslations.langCode', '=', $parsedCriteria['lang']['value'])
                        ->where('ContentTranslations.isActive', '=', 1);
                }
            );
            unset($parsedCriteria['lang']);
        } else {
            if ($this->orderByTranslation($parsedOrderBy)) {
                throw new RepositoryValidationException('Error: \'lang\' criteria is required');
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
     * Checks if provided type exists
     *
     * @param string $type    type name
     * @param array  $types   types to check
     * @param string $message exception message
     *
     * @return string
     * @throws RepositoryValidationException
     */
    private function validateType($type, $types, $message = "Content type doesn't exist")
    {
        if (in_array($type, $types)) {
            return $type;
        } else {
            throw new RepositoryValidationException($message);
        }

    }

    /**
     * Checks if we want to sort by non core field
     *
     * @param array $parsedOrderBy OrderBy array
     *
     * @return bool
     * @throws RepositoryValidationException
     */
    private function orderByTranslation($parsedOrderBy)
    {
        foreach ($parsedOrderBy as $order) {
            if (!array_key_exists('relation', $order)) {
                throw new RepositoryValidationException('OrderBy should always have relation property');
            }
            if ($order['relation'] !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle updating content parent
     *
     * @param Content $content content entity
     * @param array   $data    input data
     *
     * @return array
     * @throws RepositoryValidationException
     */
    private function handleParentUpdate(Content $content, array $data)
    {
        $parent = $this->getById($data['parentId']);

        // @TODO handle parent change for category with children
        if ($content->type === 'category') {
            // Check if category has children
            if (!empty($this->getChildren($content)->first())) {
                throw new RepositoryValidationException('You cannot change parent of not empty category');
            }
        };

        // Set parent
        if (!empty($parent)) {
            /** @TODO get registered types */
            $this->validateType(
                $parent->type,
                ['category'],
                "Content type '" . $parent->type . "' is not allowed for the parent type"
            );
            $content->setChildOf($parent);
        } else {
            $content->setAsRoot();
        }
    }

}
