<?php namespace functional;

use Gzero\Entity\File;
use Gzero\Entity\FileType;
use Gzero\Entity\User;
use Gzero\Repository\FileRepository;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Events\Dispatcher;

require_once(__DIR__ . '/../../stub/TestSeeder.php');
require_once(__DIR__ . '/../../stub/TestTreeSeeder.php');

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class FileRepositoryTest
 *
 * @package    functional
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class FileRepositoryTest extends \TestCase {

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var FileRepository
     */
    protected $repository;

    /**
     * files directory
     */
    protected $filesDir;


    protected function _before()
    {
        // Start the Laravel application
        $this->startApplication();
        $this->repository = new FileRepository(new File(), new FileType(), new Dispatcher());
        $this->filesDir   = __DIR__ . '/../../resources';
        $this->seed('TestSeeder'); // Relative to tests/app/
    }


    public function _after()
    {
        $dirName = config('gzero.upload.directory');
        if ($dirName) {
            Storage::deleteDirectory($dirName);
        }
        // Stop the Laravel application
        $this->stopApplication();
    }

    /*
     |--------------------------------------------------------------------------
     | START File tests
     |--------------------------------------------------------------------------
     */

    /**
     * @test
     */
    public function can_create_file()
    {

        $uploadedFile = $this->getExampleImage();
        $author       = User::find(1);
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'is_active'    => true,
                'info'         => ['key' => 'value'],
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile,
            $author
        );

        $newFile        = $this->repository->getById($file->id);
        $newFileAuthor  = $newFile->author;
        $newTranslation = $newFile->translations[0];

        // File
        $this->assertNotSame($file, $newFile);
        $this->assertEquals($file->id, $newFile->id);
        $this->assertEquals($file->type, $newFile->type);
        $this->assertEquals($file->name, $newFile->name);
        $this->assertEquals($file->extension, $newFile->extension);
        $this->assertEquals($file->is_active, $newFile->is_active);
        $this->assertEquals($file->size, $newFile->size);
        $this->assertEquals($file->mime_type, $newFile->mime_type);
        $this->assertEquals($file->info, $newFile->info);

        // Author
        $this->assertEquals($author->id, $newFile->created_by);
        $this->assertEquals($author->email, $newFileAuthor['email']);

        // Translation
        $this->assertEquals($newTranslation->lang_code, 'en');
        $this->assertEquals($newTranslation->title, 'Example file title');
        $this->assertEquals($newTranslation->description, 'Example file description');
    }

    /**
     * @test
     */
    public function can_create_file_without_author()
    {
        $uploadedFile = $this->getExampleImage();
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'is_active'    => true,
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );
        $newFile      = $this->repository->getById($file->id);
        $this->assertNotSame($file, $newFile);
        $this->assertEquals($file->name, $newFile->name);
    }

    /**
     * @test
     */
    public function can_create_file_without_translation()
    {
        $uploadedFile = $this->getExampleImage();
        $file         = $this->repository->create(
            [
                'type'      => 'image',
                'is_active' => true
            ],
            $uploadedFile
        );
        $newFile      = $this->repository->getById($file->id);
        $this->assertNotSame($file, $newFile);
        $this->assertEquals($file->name, $newFile->name);
    }

    /**
     * @test
     */
    public function can_create_file_with_unique_name_if_file_name_is_already_taken()
    {
        $uploadedFile = $this->getExampleImage();
        $file         = $this->repository->create(
            [
                'type'      => 'image',
                'is_active' => true
            ],
            $uploadedFile
        );
        $secondFile   = $this->repository->create(
            [
                'type'      => 'image',
                'is_active' => true
            ],
            $uploadedFile
        );
        $thirdFile    = $this->repository->create(
            [
                'type'      => 'image',
                'is_active' => true
            ],
            $uploadedFile
        );
        // Delete second file
        $this->repository->delete($secondFile);
        $fourthFile = $this->repository->create(
            [
                'type'      => 'image',
                'is_active' => true
            ],
            $uploadedFile
        );
        // Delete first file
        $this->repository->delete($file);
        // Re upload first file
        $fifthFile = $this->repository->create(
            [
                'type'      => 'image',
                'is_active' => true
            ],
            $uploadedFile
        );
        // Document file
        $documentFile = $this->repository->create(
            [
                'type'      => 'document',
                'is_active' => true
            ],
            $this->getExampleDocument()
        );
        // Images file
        $this->assertEquals('example', $file->name);
        $this->assertEquals('example-1', $secondFile->name);
        $this->assertEquals('example-2', $thirdFile->name);
        $this->assertEquals('example-3', $fourthFile->name);
        $this->assertEquals('example', $fifthFile->name);
        // Document file
        $this->assertEquals('example', $documentFile->name);
    }

    /**
     * @test
     */
    public function can_create_and_get_file_translation()
    {
        $uploadedFile     = $this->getExampleImage();
        $file             = $this->repository->create(
            [
                'type'         => 'image',
                'is_active'    => true,
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'New example body',
                ]
            ],
            $uploadedFile
        );
        $newFile          = $this->repository->getById($file->id);
        $firstTranslation = $newFile->translations[0];
        // Update english translation
        $translationEn = $this->repository->createTranslation(
            $newFile,
            [
                'lang_code'   => 'en',
                'title'       => 'Updated example title',
                'description' => 'Updated example body',
            ]
        );
        // Add new polish translation
        $translationPl    = $this->repository->createTranslation(
            $newFile,
            [
                'lang_code'   => 'pl',
                'title'       => 'New polish title',
                'description' => 'New polish body',
            ]
        );
        $newTranslationEn = $this->repository->getFileTranslationById($newFile, $translationEn->id);
        $newTranslationPl = $this->repository->getFileTranslationById($newFile, $translationPl->id);
        $this->assertNotSame($file, $newFile);
        $this->assertNotSame($translationEn, $newTranslationEn);
        $this->assertNotSame($translationPl, $newTranslationPl);

        // Check if first english translation has been removed
        $foundTranslation = $this->repository->getFileTranslationById($newFile, $firstTranslation->id);
        $this->assertNull($foundTranslation);

        // Check if a new translations has been added
        // English
        $this->assertEquals($translationEn->lang_code, $newTranslationEn->lang_code);
        $this->assertEquals($translationEn->title, $newTranslationEn->title);
        $this->assertEquals($translationEn->description, $newTranslationEn->description);
        $this->assertEquals($newFile->id, $newTranslationEn->file_id);
        // Polish
        $this->assertEquals($translationPl->lang_code, $newTranslationPl->lang_code);
        $this->assertEquals($translationPl->title, $newTranslationPl->title);
        $this->assertEquals($translationPl->description, $newTranslationPl->description);
        $this->assertEquals($newFile->id, $newTranslationPl->file_id);
    }

    /**
     * @test
     */
    public function can_set_file_as_inactive()
    {
        $uploadedFile = $this->getExampleImage();
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );
        $this->repository->update(
            $file,
            [
                'is_active' => false,
            ]
        );

        $newFile = $this->repository->getById($file->id);

        // File
        $this->assertEquals(0, $newFile->is_active);

    }

    /**
     * @test
     */
    public function can_delete_file()
    {
        $uploadedFile = $this->getExampleImage();
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );

        $newFile        = $this->repository->getById($file->id);
        $newTranslation = $newFile->translations[0];

        // Add new polish translation
        $translationPl = $this->repository->createTranslation(
            $newFile,
            [
                'lang_code'   => 'pl',
                'title'       => 'New polish title',
                'description' => 'New polish body',
            ]
        );

        // Delete file and all related translations
        $this->repository->delete($newFile);
        // File
        $found = $this->repository->getById($newFile->id);
        $this->assertNull($found);
        // File translations
        $foundTranslation = $this->repository->getFileTranslationById($newFile, $newTranslation->id);
        $this->assertNull($foundTranslation);
        $foundTranslationPl = $this->repository->getFileTranslationById($newFile, $translationPl->id);
        $this->assertNull($foundTranslationPl);
        // File itself
        $this->assertFalse(Storage::exists($newFile->getUploadPath() . $newFile->getFileName()));
    }

    /**
     * @test
     */
    public function can_delete_file_translation()
    {
        $uploadedFile = $this->getExampleImage();
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );

        $newFile        = $this->repository->getById($file->id);
        $newTranslation = $newFile->translations[0];

        // Delete file translation
        $this->repository->deleteTranslation($newTranslation);
        // File translations
        $foundTranslation = $this->repository->getFileTranslationById($newFile, $newTranslation->id);
        $this->assertNull($foundTranslation);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage File type is invalid
     */
    public function it_checks_existence_of_file_type()
    {
        $uploadedFile = $this->getExampleImage();
        $this->repository->create(
            [
                'type'         => 'fakeType',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );
    }

    /**
     * @test
     * @expectedException \Gzero\Core\Handler\File\FileHandlerException
     * @expectedExceptionMessage The extension of this file (.png) is not allowed for video files
     */
    public function it_validates_allowed_file_extension()
    {
        $uploadedFile = $this->getExampleImage();
        $this->repository->create(
            [
                'type' => 'video'
            ],
            $uploadedFile
        );
    }

    /*
     |--------------------------------------------------------------------------
     | END File tests
     |--------------------------------------------------------------------------
     */


    /*
    |--------------------------------------------------------------------------
    | START List tests
    |--------------------------------------------------------------------------
    */

    /**
     * @test
     */
    public function can_filter_files_list_by_type()
    {
        $uploadedFile = $this->getExampleImage();
        // Image file
        $firstFile = $this->repository->create(
            [
                'type'         => 'image',
                'is_active'    => true,
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );

        // Document file
        $secondFile = $this->repository->create(
            [
                'type'         => 'document',
                'is_active'    => true,
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $this->getExampleDocument()
        );

        // Get files
        $files = $this->repository->getFiles(
            [
                ['type', '=', 'image'],
                ['is_active', '=', true]
            ]
        );

        // Check results
        foreach ($files as $file) {
            $this->assertEquals($firstFile->type, $file->type);
            $this->assertNotEquals($secondFile->type, $file->type);
            $this->assertEquals(true, $file->is_active);
        }
    }

    /**
     * @test
     */
    public function can_sort_files_list()
    {
        $uploadedFile = $this->getExampleImage();
        $firstFile    = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'A file title',
                    'description' => 'A file description'
                ]
            ],
            $uploadedFile
        );

        $secondFile = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'B file title',
                    'description' => 'B file description'
                ]
            ],
            $uploadedFile
        );

        // Ascending
        $files = $this->repository->getFiles(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['created_at', 'ASC'],
                ['translations.title', 'ASC'],
            ]
        );
        // Created at
        $this->assertEquals($firstFile->created_at, $files[0]['created_at']);
        // Translations title
        $this->assertEquals('A file title', $files[0]['translations'][0]['title']);

        // Descending
        $files = $this->repository->getFiles(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['created_at', 'DESC'],
                ['translations.title', 'DESC'],
            ]
        );
        // Created at
        $this->assertEquals($secondFile->created_at, $files[0]['created_at']);
        // Translations title
        $this->assertEquals('B file title', $files[0]['translations'][0]['title']);
    }

    /**
     * @test
     */
    public function can_paginate_files_list()
    {
        $uploadedFile = $this->getExampleImage();
        $firstFile    = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );

        $secondFile = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );

        // First Page
        $files = $this->repository->getFiles(
            [],
            [
                ['created_at', 'ASC'],
            ],
            1, // Page
            1 // Items per page
        );

        // First file
        $this->assertEquals(1, count($files)); // Items per page
        $this->assertEquals($firstFile->type, $files[0]->type);
        $this->assertEquals($firstFile['translations'][0]['title'], $files[0]['translations'][0]['title']);
        $this->assertEquals($firstFile['translations'][0]['lang_code'], $files[0]['translations'][0]['lang_code']);

        // Second Page
        $files = $this->repository->getFiles(
            [],
            [
                ['created_at', 'ASC'],
            ],
            2, // Page
            1 // Items per page
        );
        // Second file
        $this->assertEquals(1, count($files));
        $this->assertEquals($secondFile->type, $files[0]->type);
        $this->assertEquals($secondFile['translations'][0]['title'], $files[0]['translations'][0]['title']);
        $this->assertEquals($secondFile['translations'][0]['lang_code'], $files[0]['translations'][0]['lang_code']);
    }

    /*
    |--------------------------------------------------------------------------
    | END List tests
    |--------------------------------------------------------------------------
    */

    private function getExampleImage()
    {
        return new UploadedFile($this->filesDir . '/example.png', 'example.png', 'image/jpeg', null, null, true);
    }

    private function getExampleDocument()
    {
        return new UploadedFile($this->filesDir . '/example.txt', 'example.txt', 'text/plain', null, null, true);
    }

}
