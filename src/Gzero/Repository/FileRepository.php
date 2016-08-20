<?php namespace Gzero\Repository;

use Gzero\Entity\File;
use Gzero\Entity\FileTranslation;
use Gzero\Entity\FileType;
use Gzero\Entity\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * File repository constructor
     *
     * @param File       $file     File model
     * @param FileType   $fileType file type model
     * @param Dispatcher $events   Events dispatcher
     */
    public function __construct(File $file, FileType $fileType, Dispatcher $events)
    {
        $this->model     = $file;
        $this->typeModel = $fileType;
        $this->events    = $events;
    }

    /**
     * Create specific file entity
     *
     * @param array        $data         Array with all required fields to persist
     * @param UploadedFile $uploadedFile The object returned by the Request file method
     * @param User|null    $author       Author entity
     *
     * @return File
     * @throws \Exception
     */
    public function create(Array $data, UploadedFile $uploadedFile, User $author = null)
    {
        if ($uploadedFile->isValid()) {
            $file = $this->newQuery()->transaction(
                function () use ($data, $author, $uploadedFile) {
                    // File PHP resource for the resource put method, which will use Flysystem's underlying stream support.
                    $resource     = fopen($uploadedFile->getRealPath(), 'r');
                    $data         = array_merge($data, $this->getFileData($uploadedFile));
                    $translations = array_get($data, 'translations'); // Nested relation fields
                    if (array_key_exists('type', $data)) {
                        $type = $this->typeModel->resolveType($this->validateType($data['type']));
                        $file = new File();
                        $file->fill($data);
                        $type->validateExtension($file);
                        // prepare file name
                        $path = $file->getUploadPath() . $uploadedFile->getClientOriginalName();
                        if (Storage::has($path)) {
                            $file->name = $this->getUniqueFileName($file->getUploadPath(), $uploadedFile);
                            $path       = $file->getUploadPath() . $file->name . '.' . $file->extension;
                        }
                        // put file in storage
                        if (Storage::getDefaultDriver() === 's3') { // fix for the wrong mime types on s3
                            Storage::disk('s3')->getDriver()->getAdapter()->getClient()->putObject(
                                [
                                    'Bucket'      => config('filesystems.disks.s3.bucket'),
                                    'Key'         => $path,
                                    'Body'        => file_get_contents($uploadedFile),
                                    'ContentType' => $file->mimeType
                                ]
                            );
                        } else {
                            Storage::put($path, $resource);
                        }

                        $this->events->fire('file.creating', [$file, $author]);
                        if ($author) {
                            $file->author()->associate($author);
                        }
                        $file->save();
                        // File translations
                        if (!empty($translations)) {
                            $this->createTranslation($file, $translations);
                            $this->events->fire('file.created', [$file]);
                        }
                        return $this->getById($file->id);
                    } else {
                        throw new RepositoryException("File type is required");
                    }
                }
            );
            return $file;
        };
        throw new RepositoryException("Error occurred while uploading the file to the server");
    }

    /**
     * Creates translation for specified file entity
     *
     * @param File  $file File entity
     * @param array $data new data to save
     *
     * @return FileTranslation
     * @throws RepositoryException
     */
    public function createTranslation(File $file, Array $data)
    {
        if (array_key_exists('langCode', $data) && array_key_exists('title', $data)) {
            // New translation query
            $translation = $this->newQuery()->transaction(
                function () use ($file, $data) {
                    // Remove any existing translation in this language
                    $existingTranslation = $this->getFileTranslationByLangCode($file, $data['langCode']);
                    if ($existingTranslation) {
                        $existingTranslation->delete();
                    }
                    $translation = new FileTranslation();
                    $translation->fill($data);
                    $this->events->fire('file.translation.creating', [$file, $translation]);
                    $file->translations()->save($translation);
                    $this->events->fire('file.translation.created', [$file, $translation]);
                    return $this->getFileTranslationById($file, $translation->id);
                }
            );
            return $translation;
        } else {
            throw new RepositoryException("Language code and title of translation is required");
        }
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
    public function update(File $file, Array $data, User $modifier = null)
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
                $url = $file->getUploadPath() . $file->getFileName();
                if (Storage::has($url)) {
                    Storage::delete($url);
                }
                $file->delete();
                $file->translations()->delete();
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
    public function getFileTranslationById(File $file, $id)
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
    public function getFileTranslationByLangCode(File $file, $langCode)
    {
        return $file->translations()->where('langCode', '=', $langCode)->first();
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
     * @return Collection
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
     * Default order for user query
     *
     * @return callable
     */
    protected function fileDefaultOrderBy()
    {
        return function ($query) {
            $query->orderBy('Files.createdAt', 'DESC');
        };
    }

    /**
     * Handle joining content translations table based on provided criteria
     *
     * @param array $parsedCriteria Array with filter criteria
     * @param array $parsedOrderBy  Array with orderBy
     * @param mixed $query          Eloquent query object
     *
     * @throws RepositoryException
     * @return array
     */
    private function handleTranslationsJoin(array &$parsedCriteria, array $parsedOrderBy, $query)
    {
        if (!empty($parsedCriteria['lang'])) {
            $query->leftJoin(
                'FileTranslations',
                function ($join) use ($parsedCriteria) {
                    $join->on('Files.id', '=', 'FileTranslations.fileId')
                        ->where('FileTranslations.langCode', '=', $parsedCriteria['lang']['value']);
                }
            );
            unset($parsedCriteria['lang']);
        } else {
            if ($this->orderByTranslation($parsedOrderBy)) {
                throw new RepositoryException('Repository Validation Error: \'lang\' criteria is required');
            }
        }
    }


    /**
     * Checks if provided type exists
     *
     * @param string $type    type name
     * @param string $message exception message
     *
     * @return string
     * @throws RepositoryException
     */
    private function validateType($type, $message = "File type is invalid")
    {
        if (in_array($type, $this->typeModel->getActiveTypes())) {
            return $type;
        } else {
            throw new RepositoryException($message);
        }
    }

    /**
     * Checks if we want to sort by non core field
     *
     * @param array $parsedOrderBy OrderBy array
     *
     * @return bool
     * @throws RepositoryException
     */
    private function orderByTranslation($parsedOrderBy)
    {
        foreach ($parsedOrderBy as $order) {
            if (!array_key_exists('relation', $order)) {
                throw new RepositoryException('OrderBy should always have relation property');
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
     * @param UploadedFile $uploadedFile The object returned by the Request file method
     *
     * @return array with file related fields
     */
    private function getFileData(UploadedFile $uploadedFile)
    {
        return [
            'name'      => pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'size'      => $uploadedFile->getSize(),
            'mimeType'  => $uploadedFile->getMimeType(),
        ];
    }

    /**
     * Function returns an unique file name based on files already located in provided storage directory
     *
     * @param string       $directory    string storage directory to search in
     * @param UploadedFile $uploadedFile The object returned by the Request file method
     *
     * @return string $fileName an unique file name
     */
    private function getUniqueFileName($directory, UploadedFile $uploadedFile)
    {
        $files    = Storage::files($directory);
        $fileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $count    = 0;
        foreach ($files as $file) {
            if (preg_match("'^$fileName($|-[0-9]+$)'", pathinfo($file, PATHINFO_FILENAME))) {
                $count++;
            };
        }
        // check again for duplicated file name, which may be added manually e.g 'public/images/example-1.png'
        $path = $directory . $fileName . '-' . $count . '.' . $uploadedFile->getClientOriginalExtension();
        if (Storage::has($path)) {
            $count++;
        }
        return ($count) ? $fileName . '-' . $count : $fileName;
    }

}
