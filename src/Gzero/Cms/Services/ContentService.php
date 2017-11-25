<?php namespace Gzero\Cms\Services;

use Gzero\Core\Models\User;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Cms\Models\ContentType;
use Gzero\Core\Models\Route;
use Gzero\Core\Exception;
use Gzero\Core\Repositories\RepositoryException;
use Gzero\Core\Repositories\RepositoryValidationException;
use Gzero\EloquentTree\Model\Tree;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Events\Dispatcher;

class ContentService extends BaseService {

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
     *
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
     * @return ContentType|Builder
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
     * @return ContentTranslation|Builder
     */
    public function getTranslationById($id)
    {
        return $this->newORMQuery()->getRelation('translations')->getQuery()->find($id);
    }

    /**
     * Get all content's titles translation from url slug.
     *
     * @param string $url  Content url
     * @param string $lang Current lang in use
     *
     * @return array
     */
    public function getTitlesTranslationFromUrl(string $url, string $lang)
    {
        $node       = $this->getByUrl($url, $lang);
        $contentIds = array_filter(explode('/', $node->path));

        return $this->newORMQuery()
            ->getRelation('translations')->getQuery()
            ->whereIn('content_id', $contentIds)
            ->where('language_code', $lang)
            ->where('is_active', true)
            ->select('title')
            ->get()
            ->toArray();
    }


    /**
     * Match content titles with urls.
     *
     * @param array  $titles     Content titles
     * @param string $contentUrl Content url
     * @param string $lang       Current lang in use
     *
     * @return array
     */
    public function matchTitlesWithUrls(array $titles, string $contentUrl, string $lang)
    {
        $urlParts      = explode('/', $contentUrl);
        $fullUrl       = '';
        $titlesAndUrls = [];

        foreach ($urlParts as $key => $urlPart) {
            if (array_key_exists($key - 1, $urlParts)) {
                $titlesAndUrls[$key]['title'] = $titles[$key]['title'];
                $titlesAndUrls[$key]['url']   = $fullUrl . '/' . $urlPart;

                $fullUrl = $titlesAndUrls[$key]['url'];
            } else {
                $titlesAndUrls[$key]['title'] = $titles[$key]['title'];
                $titlesAndUrls[$key]['url']   = '/' . $lang . '/' . $urlPart;

                $fullUrl = $titlesAndUrls[$key]['url'];
            }
        }

        return $titlesAndUrls;
    }

    /**
     * Get content route by id.
     *
     * @param int $id Content route id
     *
     * @return Route|Builder
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
     * @return Content|Builder
     * @throws RepositoryException
     */
    public function getByUrl($url, $langCode)
    {
        return $this->newORMQuery()
            ->join(
                'routes',
                function ($join) {
                    $join->on('contents.id', '=', 'routes.routable_id')
                        ->where('routes.routable_type', '=', Content::class);
                }
            )
            ->join(
                'route_translations',
                function ($join) use ($langCode) {
                    $join->on('routes.id', '=', 'route_translations.route_id')
                        ->where('route_translations.language_code', '=', $langCode);
                }
            )
            ->where('route_translations.path', '=', $url)
            ->where('route_translations.is_active', '=', 1)// We only need content with active route
            ->first(['contents.*']);
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
            function ($query) {
                // default order by
                $query->orderBy('content_translations.is_active', 'DESC');
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
    public function getContentsByLevel(
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
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
    public function getDeletedContents(
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
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
     * Update specific content entity
     *
     * @param Content   $content  Content entity
     * @param array     $data     new data to save
     * @param User|null $modifier User entity
     *
     * @return Content
     * @SuppressWarnings("unused")
     */
    public function update(Content $content, array $data, User $modifier = null)
    {
        $content = $this->newQuery()->transaction(
            function () use ($content, $data, $modifier) {
                $content->fill($data);
                $this->events->fire('content.updating', [$content]);
                $this->handleThumbAssociation($data, $content);
                if (!empty($data['parent_id'])) {
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
     * Delete specific content translation entity
     *
     * @param ContentTranslation $translation entity to delete
     *
     * @return bool
     * @throws RepositoryValidationException
     */
    public function deleteTranslation(ContentTranslation $translation)
    {
        if ($translation->is_active) {
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
            $query->orderBy('contents.weight', 'ASC');
            $query->orderBy('contents.created_at', 'DESC');
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
                'content_translations',
                function ($join) use ($parsedCriteria) {
                    $join->on('contents.id', '=', 'content_translations.content_id')
                        ->where('content_translations.language_code', '=', $parsedCriteria['lang']['value'])
                        ->where('content_translations.is_active', '=', 1);
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
            'users',
            function ($join) {
                $join->on('contents.author_id', '=', 'users.id');
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
        $results->load('route.translations', 'translations', 'author', 'thumb', 'thumb.translations');
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
        $parent = $this->getById($data['parent_id']);

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

    /**
     * Handle associating thumb
     *
     * @param array   $data    Data array
     * @param Content $content Content entity
     *
     * @throws RepositoryException
     * @return void
     */
    private function handleThumbAssociation(array $data, Content $content)
    {
        if (array_key_exists('thumb_id', $data)) {
            if (!empty($data['thumb_id'])) {
                $thumb = File::find($data['thumb_id']);
                if (empty($thumb)) {
                    throw new RepositoryException('Thumb does not exist');
                }
                $content->thumb()->associate($thumb);
            } else {
                $content->thumb()->dissociate();
            }
        }
    }

}
