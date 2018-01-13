<?php namespace Gzero\Cms\Services;

use Gzero\Core\Models\User;
use Gzero\Core\Query\QueryBuilder;
use Gzero\Cms\Models\File;
use Gzero\Cms\Models\FileTranslation;
use Gzero\Cms\Models\FileType;
use Gzero\Cms\Models\Uploadable;
use Gzero\Core\Repositories\RepositoryException;
use Gzero\Core\Repositories\RepositoryValidationException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;

class FileService extends BaseService {

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
        $this->filesystem = $filesystem->disk(config('gzero-cms.upload.disk'));
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
     * Attaches selected files to specified uploadable entity in database
     * HINT: use assoc array to pass arguments to pivot table
     *
     * @param Uploadable $entity   Uploadable entity
     * @param array      $filesIds files id's to attach
     *
     * @return EloquentCollection
     * @throws RepositoryValidationException
     */
    public function syncWith(Uploadable $entity, array $filesIds)
    {
        if (!$entity::checkIfExists($entity->id)) {
            throw new RepositoryValidationException("Entity does not exist");
        }

        $notInDB = File::checkIfMultipleExists($this->getFieldIdsFromSyncData($filesIds)->toArray());
        if ($notInDB->count() > 0) {
            throw new RepositoryValidationException("File ids [" . $notInDB->implode(', ') . "] does not exist");
        }

        $response = $this->newQuery()->transaction(
            function () use ($entity, $filesIds) {
                $this->events->fire('files.syncing', [$entity, $filesIds]);
                $response = $entity->files()->sync($filesIds);
                $this->events->fire('files.synced', [$entity, $filesIds]);
                return $response;
            }
        );

        return $response;
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
        if (!array_key_exists('language_code', $data) || !array_key_exists('title', $data)) {
            throw new RepositoryValidationException("Language code and title of translation is required");
        }
        // New translation query
        $translation = $this->newQuery()->transaction(
            function () use ($file, $data) {
                // Remove any existing translation in this language
                $existingTranslation = $this->getTranslationByLangCode($file, $data['language_code']);
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
                $file->blocks()->detach($file);
                $file->contents()->detach($file);
                $file->delete();
                $file->translations()->delete();
                //@TODO remove croppa thumbnails
                $this->events->fire('file.deleted', [$file]);
                return true;
            }
        );
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
        return $file->translations()->where('language_code', '=', $langCode)->first();
    }


    /**
     * Get all files with specific criteria
     *
     * @param QueryBuilder $repoQuery Repository query object
     * @param int|null     $page      Page number (if null == disabled pagination)
     *
     * @return EloquentCollection
     */
    public function getFiles(QueryBuilder $repoQuery, $page = 1)
    {
        $query  = $this->newORMQuery();
        $parsed = $this->parseArgs($repoQuery->getFilters(), $repoQuery->getSorts());
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($this->getTableName(), $query, $parsed['filter']);
        $this->handleOrderBy(
            $this->getTableName(),
            $parsed['orderBy'],
            $query,
            $this->fileDefaultOrderBy()
        );
        if ($repoQuery->hasSearchQuery()) {
            $query->where('name', 'like', '%' . $repoQuery->getSearchQeury() . '%');
        }
        return $this->handlePagination($this->getTableName(), $query, $page, $repoQuery->getPageSize());
    }

    /**
     * Get all files to specific content
     *
     * @param Uploadable $entity   Content content
     * @param array      $criteria Filter criteria
     * @param array      $orderBy  Array of columns
     * @param int|null   $page     Page number (if null == disabled pagination)
     * @param int|null   $pageSize Limit results
     *
     * @throws RepositoryException
     * @return EloquentCollection
     */
    public function getEntityFiles(
        Uploadable $entity,
        array $criteria = [],
        array $orderBy = [],
        $page = 1,
        $pageSize = self::ITEMS_PER_PAGE
    ) {
        $query  = $entity->files(false);
        $table  = $query->getModel()->getTable();
        $parsed = $this->parseArgs($criteria, $orderBy);
        $this->handleTranslationsJoin($parsed['filter'], $parsed['orderBy'], $query);
        $this->handleFilterCriteria($table, $query, $parsed['filter']);
        $this->handleOrderBy(
            $table,
            $parsed['orderBy'],
            $query,
            function ($query) {
                // default order by
                $query->orderBy('uploadables.weight', 'ASC');
            }
        );
        return $this->handlePagination($table, $query, $page, $pageSize);
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
                        ->where('file_translations.language_code', '=', $parsedCriteria['lang']['value']);
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
        $results->load('translations');
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
     * @param array        $data         file data
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
     * Resolves file type based on type in data array
     *
     * @param array $allData data array
     *
     * @return \Gzero\Core\Handler\File\FileTypeHandler
     */
    protected function resolveType(array $allData)
    {
        return $this->typeModel->resolveType($this->validateType($allData['type']));
    }

    /**
     * Extracts file id's from assoc array with mixed id's and arguments for sync call
     * e.g: [1 => ['weight' => 3], 5, 8, 10 => ['weight' => 2]]
     *
     * @param array $filesIds array with id's and arguments to pivot table
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getFieldIdsFromSyncData(array $filesIds)
    {
        return collect($filesIds)->map(
            function ($key, $value) {
                if (is_array($key)) {
                    return $value;
                }

                return $key;
            }
        );
    }

}
