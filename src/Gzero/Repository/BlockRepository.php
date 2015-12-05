<?php namespace Gzero\Repository;

use Gzero\Entity\Block;
use Gzero\Entity\BlockTranslation;
use Gzero\Entity\User;
use Gzero\Entity\Widget;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;

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
     * @var Block
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
     * @param Block      $block  Content model
     * @param Dispatcher $events Events dispatcher
     */
    public function __construct(Block $block, Dispatcher $events)
    {
        $this->model  = $block;
        $this->events = $events;
    }

    /**
     * Create specific block entity
     *
     * @param array     $data   Content entity to persist
     * @param User|null $author Author entity
     *
     * @return Block
     */
    public function create(Array $data, User $author = null)
    {
        $block = $this->newQuery()->transaction(
            function () use ($data, $author) {
                $translations = array_get($data, 'translations'); // Nested relation fields
                if (!empty($translations) && array_key_exists('type', $data)) {
                    /** @TODO get registered types */
                    $this->validateType($data['type'], ['basic', 'menu', 'slider', 'widget', 'content']);
                    $block = new Block();
                    $block->fill($data);
                    /** @TODO How to set blockable polymorphic relation here, based on type ? */
                    if ($data['type'] === 'widget') {
                        $block->blockable()->associate(Widget::create($data['widget']));
                    }
                    if ($author) {
                        $block->author()->associate($author);
                    }
                    $block->save();
                    // Block translations
                    $this->createTranslation($block, $translations);
                    return $block;
                } else {
                    throw new RepositoryException("Block type and translation is required");
                }
            }
        );
        $this->events->fire('block.created', [$block]);
        return $block;
    }

    /**
     * Creates translation for specified block entity
     *
     * @param Block $block Content entity
     * @param array $data  new data to save
     *
     * @return BlockTranslation
     * @throws RepositoryException
     */
    public function createTranslation(Block $block, Array $data)
    {
        if (array_key_exists('langCode', $data) && array_key_exists('title', $data)) {
            // New translation query
            $translation = $this->newQuery()->transaction(
                function () use ($block, $data) {
                    // Set all translation of this block as inactive
                    $this->disableActiveTranslations($block->id, $data['langCode']);
                    $translation = new BlockTranslation();
                    $translation->fill($data);
                    $translation->isActive = 1; // Because only recent translation is active
                    $block->translations()->save($translation);
                    return $translation;
                }
            );
            $this->events->fire('block.translation.created', [$block, $translation]);
            return $translation;
        } else {
            throw new RepositoryException("Language code and title of translation is required");
        }
    }

    /**
     * Get translation of specified block by id.
     *
     * @param Block $block Block entity
     * @param int   $id    Block Translation id
     *
     * @return BlockTranslation
     */
    public function getBlockTranslationById(Block $block, $id)
    {
        return $block->translations(false)->where('id', '=', $id)->first();
    }

    /**
     * Get all blocks with specific criteria
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getBlocks(array $criteria = [], array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query  = $this->newORMQuery();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->blockDefaultOrderBy()
        );
        return $this->handlePagination($this->getTableName(), $query, $page, $pageSize);
    }

    /**
     * Get all soft deleted blocks with specific criteria
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getDeletedBlocks(array $criteria = [], array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query  = $this->newORMQuery();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->blockDefaultOrderBy()
        );
        return $this->handlePagination($this->getTableName(), $query, $page, $pageSize);
    }

    /**
     * Eager load relations for eloquent collection.
     * We use this function in handlePagination method!
     *
     * @param EloquentCollection $results Eloquent collection
     *
     * @return void
     */
    protected function listEagerLoad($results)
    {
        $results->load('translations', 'author', 'blockable');
    }

    /**
     * Default order for user query
     *
     * @return callable
     */
    protected function blockDefaultOrderBy()
    {
        return function ($query) {
            $query->orderBy('id', 'DESC');
        };
    }

    /**
     * Checks if provided type exists
     *
     * @param string $type    type name
     * @param array  $types   types to check
     * @param string $message exception message
     *
     * @return string
     * @throws RepositoryException
     */
    private function validateType($type, $types, $message = "Block type doesn't exist")
    {
        if (in_array($type, $types)) {
            return $type;
        } else {
            throw new RepositoryException($message);
        }

    }
}
