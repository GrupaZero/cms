<?php namespace Gzero\Repository;

use Gzero\Entity\Block;
use Gzero\Entity\Content;
use Gzero\Entity\File;
use Gzero\Entity\FileTranslation;
use Gzero\Entity\FileType;
use Gzero\Entity\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class FileRepository
 *
 * @package    Gzero\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class FileRepository extends BaseRepository {

    /**
     * @var File
     */
    protected $model;

    /**
     * The events dispatcher
     *
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var FilesystemAdapter
     */
    protected $filesystem;

    /**
     * File repository constructor
     *
     * @param File              $file       File model
     * @param FileType          $fileType   file type model
     * @param Dispatcher        $events     Events dispatcher
     * @param FilesystemManager $filesystem File system manager
     */
    public function __construct(File $file, FileType $fileType, Dispatcher $events, FilesystemManager $filesystem)
    {
        $this->model      = $file;
        $this->typeModel  = $fileType;
        $this->events     = $events;
        $this->filesystem = $filesystem->disk(config('gzero.upload.disk'));
    }

    /**
     * Create specific file entity
     *
     * @param array        $data         Array with all required fields to persist
     * @param UploadedFile $uploadedFile The object returned by the Request file method
     * @param User|null    $author       Author entity
     *
     * @return File
     * @throws RepositoryValidationException
     */
    public function create(array $data, UploadedFile $uploadedFile, User $author = null)
    {
        $allData = $this->getAllFileData($data, $uploadedFile);
        if (empty($allData['type'])) {
            throw new RepositoryValidationException("File type is required");
        }

        $type = $this->resolveType($allData);
        $type->validateExtension($uploadedFile->getClientOriginalExtension());

        $file = $this->newQuery()->transaction(
            function () use ($allData, $author, $uploadedFile) {
                [$fileName, $fileNameWithExtension] = $this->getUniqueFileName($allData);
                $file = new File();
                $file->fill($allData);
                $file->name = $fileName; // Split extension
                $this->events->fire('file.creating', [$file, $author]);
                if ($author) {
                    $file->author()->associate($author);
                }
                $file->save();
                // File translations
                if (!empty($allData['translations'])) {
                    $this->createTranslation($file, $allData['translations']);
                    $this->events->fire('file.created', [$file]);
                }

                $this->filesystem->putFileAs($file->getUploadPath(), $uploadedFile, $fileNameWithExtension);
                return $this->getById($file->id);
            }
        );

        return $file;
    }

    /**
     * Attaches selected files to specified content entity in database
     *
     * @param Content $content  Content entity
     * @param array   $filesIds files id's to attach
     *
     * @return EloquentCollection
     * @throws RepositoryValidationException
     */
    public function attachToContent(Content $content, array $filesIds)
    {
        // @TODO Handle single file too
        // @TODO Add info about passing additional arguments to pivot table
        // @TODO Add syncWith
        if (empty($filesIds)) {
            throw new RepositoryValidationException('You must provide the files in order to add them to the content');
        }

        $this->checkIfFilesExists($filesIds);

        // New content query
        $content = $this->newQuery()->transaction(
            function () use ($content, $filesIds) {
                $this->events->fire('content.files.adding', [$content, $filesIds]);
                $content->files()->sync($filesIds, false);
                $this->events->fire('content.files.added', [$content, $filesIds]);
                return $content;
            }
        );

        return $this->getContentFiles($content);
    }

    /**
     * Attaches selected files to specified block entity in database
     *
     * @param Block $block    Block entity
     * @param array $filesIds files id's to attach
     *
     * @return EloquentCollection
     * @throws RepositoryValidationException
     */
    public function attachToBlock(Block $block, array $filesIds)
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

        return $this->getBlockFiles($block);
    }

    /**
     * Creates translation for specified file entity
     *
     * @param File  $file File entity
     * @param array $data new data to save
     *
     * @return FileTranslation
     * @throws RepositoryValidationException
     */
    public function createTranslation(File $file, array $data)
    {
        if (!array_key_exists('lang_code', $data) || !array_key_exists('title', $data)) {
            throw new RepositoryValidationException("Language code and title of translation is required");
        }
        // New translation query
        $translation = $this->newQuery()->transaction(
            function () use ($file, $data) {
                // Remove any existing translation in this language
                $existingTranslation = $this->getTranslationByLangCode($file, $data['lang_code']);
                if ($existingTranslation) {
                    $existingTranslation->delete();
                }
                $translation = new FileTranslation();
                $translation->fill($data);
                $this->events->fire('file.translation.creating', [$file, $translation]);
                $file->translations()->save($translation);
                $this->events->fire('file.translation.created', [$file, $translation]);
                return $this->getTranslationById($file, $translation->id);
            }
        );
        return $translation;
    }

    /**
     * Update specific file entity
     *
     * @param File      $file     File entity
     * @param array     $data     New data to save
     * @param User|null $modifier User entity
     *
     * @return File
     * @SuppressWarnings("unused")
     */
    public function update(File $file, array $data, User $modifier = null)
    {
        $file = $this->newQuery()->transaction(
            function () use ($file, $data, $modifier) {
                $this->events->fire('file.updating', [$file, $data, $modifier]);
                $file->fill($data);
                $file->save();
                $this->events->fire('file.updated', [$file]);
                return $this->getById($file->id);
            }
        );
        return $file;
    }

    /**
     * Updates file of specified content entity
     *
     * @param Content $content    Content entity
     * @param File    $file       file to update relation
     * @param array   $attributes files attributes to update
     *
     * @return File
     * @throws RepositoryValidationException
     */
    public function updateContentFile(Content $content, File $file, array $attributes)
    {
        // New content query
        $result = $this->newQuery()->transaction(
            function () use ($content, $file, $attributes) {
                $this->events->fire('content.files.updating', [$content, $file, $attributes]);
                $result = $content->files()->updateExistingPivot($file->id, $attributes);
                $this->events->fire('content.files.updated', [$content, $file, $attributes]);
                return $result;
            }
        );

        return $result;
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
    public function updateBlockFile(Block $block, $fileId, array $attributes)
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
                return $this->getById($fileId);
            }
        );

        return $block;
    }

    /**
     * Delete specific file entity and removes file from storage
     *
     * @param File $file File entity to delete
     *
     * @return boolean
     */
    public function delete(File $file)
    {
        return $this->newQuery()->transaction(
            function () use ($file) {
                $this->events->fire('file.deleting', [$file]);
                $path = $file->getFullPath();
                if ($this->filesystem->has($path)) {
                    $this->filesystem->delete($path);
                }
                $file->delete();
                $file->translations()->delete();
                //@TODO remove croppa thumbnails
                $this->events->fire('file.deleted', [$file]);
                return true;
            }
        );
    }

    /**
     * Detaches selected files from specified content entity in database
     *
     * @param Content $content  Content entity
     * @param array   $filesIds files id's to detach
     *
     * @return EloquentCollection
     * @throws RepositoryValidationException
     */
    public function detachFromContent(Content $content, array $filesIds)
    {
        if (empty($filesIds)) {
            throw new RepositoryValidationException(
                'You must provide the files in order to remove them from the content'
            );
        }

        // New content query
        $content = $this->newQuery()->transaction(
            function () use ($content, $filesIds) {
                $this->events->fire('content.files.removing', [$content, $filesIds]);
                $content->files()->detach($filesIds);
                $this->events->fire('content.files.removed', [$content, $filesIds]);

                // Remove related file
                if (!empty($content->file_id) && in_array($content->file_id, $filesIds)) {
                    $this->events->fire('content.related.file.removing', [$content]);
                    $content->file_id = null;
                    $content->save();
                    $this->events->fire('content.related.file.removed', [$content]);
                }

                return $content;
            }
        );
        return $this->getContentFiles($content);
    }

    /**
     * Detaches selected files from specified block entity in database
     *
     * @param Block $block    Block entity
     * @param array $filesIds files id's to detach
     *
     * @return EloquentCollection
     * @throws RepositoryValidationException
     */
    public function detachFromBlock(Block $block, array $filesIds)
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
        return $this->getBlockFiles($block);
    }

    /**
     * Delete specific file translation entity
     *
     * @param FileTranslation $translation entity to delete
     *
     * @return bool
     * @throws RepositoryException
     * @throws \Exception
     */
    public function deleteTranslation(FileTranslation $translation)
    {
        return $this->newQuery()->transaction(
            function () use ($translation) {
                return $translation->delete();
            }
        );
    }

    /**
     * Get translation of specified file by id.
     *
     * @param File $file File entity
     * @param int  $id   File Translation id
     *
     * @return FileTranslation
     */
    public function getTranslationById(File $file, $id)
    {
        return $file->translations()->where('id', '=', $id)->first();
    }

    /**
     * Get translation of specified file by id.
     *
     * @param File   $file     File entity
     * @param string $langCode File Translation id
     *
     * @return FileTranslation
     */
    public function getTranslationByLangCode(File $file, $langCode)
    {
        return $file->translations()->where('lang_code', '=', $langCode)->first();
    }


    /**
     * Get all files with specific criteria
     *
     * @param array    $criteria Filter criteria
     * @param array    $orderBy  Array of columns
     * @param int|null $page     Page number (if null == disabled pagination)
     * @param int|null $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getFiles(array $criteria = [], array $orderBy = [], $page = 1, $pageSize = self::ITEMS_PER_PAGE)
    {
        $query  = $this->newORMQuery();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->fileDefaultOrderBy()
        );
        return $this->handlePagination($this->getTableName(), $query, $page, $pageSize);
    }

    /**
     * Get all files to specific content
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
    public function getContentFiles(
        Content $content,
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $content->files(false);
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getFilesTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getFilesTableName(),
            $parsed['orderBy'],
            $query,
            function ($query) {
                // default order by
                $query->orderBy('uploadables.weight', 'ASC');
            }
        );
        return $this->handlePagination($this->getFilesTableName(), $query, $page, $pageSize);
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
    public function getBlockFiles(
        Block $block,
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $block->files(false);
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getFilesTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getFilesTableName(),
            $parsed['orderBy'],
            $query,
            function ($query) {
                // default order by
                $query->orderBy('uploadables.weight', 'ASC');
            }
        );
        return $this->handlePagination($this->getFilesTableName(), $query, $page, $pageSize);
    }

    /**
     * Handle joining file translations table based on provided criteria
     *
     * @param array $parsedCriteria Array with filter criteria
     * @param array $parsedOrderBy  Array with orderBy
     * @param mixed $query          Eloquent query object
     *
     * @throws RepositoryValidationException
     * @return array
     */
    public function handleTranslationsJoin(array &$parsedCriteria, array $parsedOrderBy, $query)
    {
        if (!empty($parsedCriteria['lang'])) {
            $query->leftJoin(
                'file_translations',
                function ($join) use ($parsedCriteria) {
                    $join->on('files.id', '=', 'file_translations.file_id')
                        ->where('file_translations.lang_code', '=', $parsedCriteria['lang']['value']);
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
     * Eager load relations for eloquent collection.
     * We use this function in handlePagination method!
     *
     * @param EloquentCollection $results Eloquent collection
     *
     * @return void
     */
    protected function listEagerLoad($results)
    {
        $results->load('translations', 'author', 'contents', 'blocks');
    }

    /**
     * Default order for files query
     *
     * @return callable
     */
    protected function fileDefaultOrderBy()
    {
        return function ($query) {
            $query->orderBy('files.created_at', 'DESC');
        };
    }


    /**
     * Checks if provided type exists
     *
     * @param string $type    type name
     * @param string $message exception message
     *
     * @return string
     * @throws RepositoryValidationException
     */
    protected function validateType($type, $message = "File type is invalid")
    {
        if (in_array($type, $this->typeModel->getActiveTypes())) {
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
    protected function orderByTranslation($parsedOrderBy)
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
     * Returns file related data for database insertion
     *
     * @param array        $data
     * @param UploadedFile $uploadedFile The object returned by the Request file method
     *
     * @return array with file related fields
     */
    protected function getAllFileData(array $data, UploadedFile $uploadedFile)
    {
        return array_merge(
            $data,
            [
                'name'      => str_slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME)),
                'extension' => mb_strtolower($uploadedFile->getClientOriginalExtension()),
                'size'      => $uploadedFile->getSize(),
                'mime_type' => $uploadedFile->getMimeType(),
            ]
        );
    }

    /**
     * Function returns an unique file name based on files already located in provided storage directory
     *
     * @param array $data All data related to uploaded file
     *
     * @return array [fileName, fileNameWithExtension]
     */
    protected function getUniqueFileName(array $data)
    {
        $uFileName = $data['name'] . '.' . $data['extension'];
        $typeDir   = str_plural($data['type']);
        if (!$this->filesystem->has($typeDir . '/' . $uFileName)) {
            return [$data['name'], $uFileName];
        }
        $newName = uniqid($data['name'] . '_');
        return [$newName, $newName . '.' . $data['extension']];
    }

    /**
     * @param $allData
     *
     * @return \Gzero\Core\Handler\File\FileTypeHandler
     */
    protected function resolveType($allData)
    {
        return $this->typeModel->resolveType($this->validateType($allData['type']));
    }

}
