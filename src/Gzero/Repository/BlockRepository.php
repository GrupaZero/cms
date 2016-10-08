<?php namespace Gzero\Repository;

use Gzero\Entity\Block;
use Gzero\Entity\BlockTranslation;
use Gzero\Entity\File;
use Gzero\Entity\User;
use Gzero\Entity\Widget;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;

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
     * File repository
     *
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * Block repository constructor
     *
     * @param Block          $block          Block model
     * @param Dispatcher     $events         Events dispatcher
     * @param FileRepository $fileRepository File repository
     */
    public function __construct(Block $block, Dispatcher $events, FileRepository $fileRepository)
    {
        $this->model          = $block;
        $this->events         = $events;
        $this->fileRepository = $fileRepository;
    }

    /**
     * Create specific block entity
     *
     * @param array     $data   Array with all required fields to persist
     * @param User|null $author Author entity
     *
     * @return Block
     */
    public function create(Array $data, User $author = null)
    {
        $block = $this->newQuery()->transaction(
            function () use ($data, $author) {
                $translations = array_get($data, 'translations'); // Nested relation fields
                if (empty($translations) || !array_key_exists('type', $data)) {
                    throw new RepositoryValidationException("Block type and translation is required");
                }
                /** @TODO get registered types */
                $this->validateType($data['type'], ['basic', 'menu', 'slider', 'widget', 'content']);
                $block = new Block();
                $block->fill($data);
                $this->events->fire('block.creating', [$block, $author]);
                /** @TODO How to set blockable polymorphic relation here, based on type ? */
                if ($data['type'] === 'widget') {
                    $this->createWidget($block, $data);
                }
                if ($author) {
                    $block->author()->associate($author);
                }
                $block->save();
                // Block translations
                $this->createTranslation($block, $translations);
                $this->events->fire('block.created', [$block]);
                return $this->getById($block->id);
            }
        );
        return $block;
    }

    /**
     * Creates translation for specified block entity
     *
     * @param Block $block Block entity
     * @param array $data  new data to save
     *
     * @return BlockTranslation
     * @throws RepositoryValidationException
     */
    public function createTranslation(Block $block, Array $data)
    {
        if (!array_key_exists('langCode', $data) || !array_key_exists('title', $data)) {
            throw new RepositoryValidationException("Language code and title of translation is required");
        }
        // New translation query
        $translation = $this->newQuery()->transaction(
            function () use ($block, $data) {
                // Set all translation of this block as inactive
                $this->disableActiveTranslations($block->id, $data['langCode']);
                $translation = new BlockTranslation();
                $translation->fill($data);
                $this->events->fire('block.translation.creating', [$block, $translation]);
                $translation->isActive = 1; // Because only recent translation is active
                $block->translations()->save($translation);
                $this->events->fire('block.translation.created', [$block, $translation]);
                $this->clearBlocksCache();
                return $this->getBlockTranslationById($block, $translation->id);
            }
        );
        return $translation;
    }

    /**
     * Attaches selected files to specified block entity in database
     *
     * @param Block $block    Block entity
     * @param array $filesIds files id's to attach
     *
     * @return Block
     * @throws RepositoryValidationException
     */
    public function addFiles(Block $block, Array $filesIds)
    {
        if (empty($filesIds)) {
            throw new RepositoryValidationException('You must provide the files in order to add them to the block');
        }

        $this->checkIfFilesExists($filesIds);

        // New block query
        $block = $this->newQuery()->transaction(
            function () use ($block, $filesIds) {
                $this->events->fire('block.files.adding', [$block, $filesIds]);
                $block->files()->sync($filesIds, false);
                $this->events->fire('block.files.added', [$block, $filesIds]);
                return $block;
            }
        );

        return $this->getFiles($block);
    }

    /**
     * Update specific block entity
     *
     * @param Block     $block    Block entity
     * @param array     $data     New data to save
     * @param User|null $modifier User entity
     *
     * @return Block
     * @SuppressWarnings("unused")
     */
    public function update(Block $block, Array $data, User $modifier = null)
    {
        $block = $this->newQuery()->transaction(
            function () use ($block, $data, $modifier) {
                $this->events->fire('block.updating', [$block, $data, $modifier]);
                $block->fill($data);
                $block->save();
                $this->events->fire('block.updated', [$block]);
                $this->clearBlocksCache();
                return $this->getById($block->id);
            }
        );
        return $block;
    }

    /**
     * Updates file of specified block entity
     *
     * @param Block   $block      Block entity
     * @param integer $fileId     file id to update
     * @param array   $attributes files attributes to update
     *
     * @return Block
     * @throws RepositoryValidationException
     */
    public function updateFile(Block $block, $fileId, Array $attributes)
    {
        if (!$fileId) {
            throw new RepositoryValidationException('You must provide the file in order to update it');
        }

        // New block query
        $block = $this->newQuery()->transaction(
            function () use ($block, $fileId, $attributes) {
                $this->events->fire('block.files.updating', [$block, $fileId, $attributes]);
                $block->files()->updateExistingPivot($fileId, $attributes);
                $this->events->fire('block.files.updated', [$block, $fileId, $attributes]);
                return $this->getBlockFileById($block, $fileId);
            }
        );

        return $block;
    }

    /**
     * Delete specific block entity using softDelete
     *
     * @param Block $block Block entity to delete
     *
     * @return boolean
     */
    public function delete(Block $block)
    {
        return $this->newQuery()->transaction(
            function () use ($block) {
                $this->events->fire('block.deleting', [$block]);
                $block->delete();
                $this->events->fire('block.deleted', [$block]);
                $this->clearBlocksCache();
                return true;
            }
        );
    }

    /**
     * Delete specific block entity using forceDelete
     *
     * @param Block $block Block entity to delete
     *
     * @return boolean
     */
    public function forceDelete(Block $block)
    {
        return $this->newQuery()->transaction(
            function () use ($block) {
                $this->events->fire('block.forceDeleting', [$block]);
                /** @TODO handling delete other stuff like a menu etc. */
                $this->getByIdWithTrashed($block->id)->forceDelete();
                //$block->forceDelete();
                $this->events->fire('block.forceDeleted', [$block]);
                $this->clearBlocksCache();
                return true;
            }
        );
    }

    /**
     * Delete specific block translation entity
     *
     * @param BlockTranslation $translation entity to delete
     *
     * @return bool
     * @throws RepositoryValidationException
     * @throws \Exception
     */
    public function deleteTranslation(BlockTranslation $translation)
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
     * Detaches selected files from specified block entity in database
     *
     * @param Block $block    Block entity
     * @param array $filesIds files id's to detach
     *
     * @return Block
     * @throws RepositoryValidationException
     */
    public function removeFiles(Block $block, Array $filesIds)
    {
        if (empty($filesIds)) {
            throw new RepositoryValidationException(
                'You must provide the files in order to remove them from the block'
            );
        }

        // New block query
        $block = $this->newQuery()->transaction(
            function () use ($block, $filesIds) {
                $this->events->fire('block.files.removing', [$block, $filesIds]);
                $block->files()->detach($filesIds);
                $this->events->fire('block.files.removed', [$block, $filesIds]);
                return $block;
            }
        );
        return $this->getFiles($block);
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
     * Get block file by id.
     *
     * @param Block $block Block entity
     * @param int   $id    Block File id
     *
     * @return File
     */
    public function getBlockFileById(Block $block, $id)
    {
        return $block->files(false)->where('id', '=', $id)->withPivot('weight')->first();
    }

    /**
     * Get all files to specific block
     *
     * @param Block    $block    Block block
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getFiles(
        Block $block,
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $block->files(false);
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->fileRepository->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getFilesTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getFilesTableName(),
            $parsed['orderBy'],
            $query,
            function ($query) { // default order by
                $query->orderBy('Uploadables.weight', 'ASC');
            }
        );
        return $this->handlePagination($this->getFilesTableName(), $query, $page, $pageSize);
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
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
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
     * Get all visible blocks
     *
     * @param array $ids        Array with blocks ids returned from block finder
     * @param bool  $onlyPublic Return only public blocks
     *
     * @throws RepositoryException
     * @return Collection
     */
    public function getVisibleBlocks(array $ids, $onlyPublic = true)
    {
        $query = $this->newORMQuery();
        if (!empty($ids)) {
            $query->whereIn('id', $ids)
                ->orWhere('filter', null)
                ->orderBy('weight', 'ASC');
        } else { // blocks on all pages only
            $query->where('filter', null)
                ->orderBy('weight', 'ASC');
        }
        if ($onlyPublic) {
            $query->where('isActive', '=', true);
        }
        $blocks = $query->get();
        $this->listEagerLoad($blocks);
        return $blocks;
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
    public function getDeletedBlocks(
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $this->newORMQuery()->onlyTrashed();
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
            $query->orderBy('Blocks.weight', 'ASC');
        };
    }

    /**
     * Handle joining block translations table based on provided criteria
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
                'BlockTranslations',
                function ($join) use ($parsedCriteria) {
                    $join->on('Blocks.id', '=', 'BlockTranslations.blockId')
                        ->where('BlockTranslations.langCode', '=', $parsedCriteria['lang']['value'])
                        ->where('BlockTranslations.isActive', '=', 1);
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
     * Checks if provided type exists
     *
     * @param string $type    type name
     * @param array  $types   types to check
     * @param string $message exception message
     *
     * @return string
     * @throws RepositoryValidationException
     */
    private function validateType($type, $types, $message = "Block type doesn't exist")
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
     * Creates a block widget
     *
     * @param Block $block block entity
     * @param array $data  input data
     *
     * @return string
     * @throws RepositoryValidationException
     *
     */
    private function createWidget(Block $block, array $data)
    {
        if (array_key_exists('widget', $data) && is_array($data['widget'])) {
            $block->blockable()->associate(Widget::create($data['widget']));
        } else {
            throw new RepositoryValidationException("Widget is required");
        }
    }

    /**
     * Clears blocks cache
     *
     * @return void
     */
    private function clearBlocksCache()
    {
        Cache::forget('blocks:filter:public');
        Cache::forget('blocks:filter:admin');
    }
}
