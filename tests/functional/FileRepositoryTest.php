<?php namespace functional;

use Gzero\Entity\File;
use Gzero\Entity\FileType;
use Gzero\Entity\User;
use Gzero\Repository\FileRepository;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Events\Dispatcher;


require_once(__DIR__ . '/../stub/TestSeeder.php');
require_once(__DIR__ . '/../stub/TestTreeSeeder.php');

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
class FileRepositoryTest extends \EloquentTestCase {

    /**
     * @var FileRepository
     */
    protected $repository;

    /**
     * files directory
     */
    protected $filesDir;


    public function setUp()
    {
        parent::setUp();
        $this->repository = new FileRepository(new File(), new FileType(), new Dispatcher());
        $this->filesDir   = __DIR__ . '/../resources';
        $this->seed('TestSeeder'); // Relative to tests/app/
    }

    public function tearDown()
    {
        $dirName = config('gzero.upload.directory');
        if ($dirName) {
            Storage::deleteDirectory($dirName);
        }
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

        $uploadedFile = $this->getExampleFile();
        $author       = User::find(1);
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'isActive'     => true,
                'info'         => ['key' => 'value'],
                'translations' => [
                    'langCode'    => 'en',
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
        $this->assertEquals($file->isActive, $newFile->isActive);
        $this->assertEquals($file->size, $newFile->size);
        $this->assertEquals($file->mimeType, $newFile->mimeType);
        $this->assertEquals($file->info, $newFile->info);

        // Author
        $this->assertEquals($author->id, $newFile->createdBy);
        $this->assertEquals($author->email, $newFileAuthor['email']);

        // Translation
        $this->assertEquals($newTranslation->langCode, 'en');
        $this->assertEquals($newTranslation->title, 'Example file title');
        $this->assertEquals($newTranslation->description, 'Example file description');
    }

    /**
     * @test
     */
    public function can_create_file_without_author()
    {
        $uploadedFile = $this->getExampleFile();
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'isActive'     => true,
                'translations' => [
                    'langCode'    => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );
        $newFile      = $this->repository->getById($file->id);
        $this->assertNotSame($file, $newFile);
        $this->assertNull($newFile->author);
    }

    /**
     * @test
     */
    public function can_create_and_get_file_translation()
    {
        $uploadedFile     = $this->getExampleFile();
        $file             = $this->repository->create(
            [
                'type'         => 'image',
                'isActive'     => true,
                'translations' => [
                    'langCode'    => 'en',
                    'title'       => 'Example file title',
                    'description' => 'New example body',
                ]
            ],
            $uploadedFile
        );
        $newFile          = $this->repository->getById($file->id);
        $firstTranslation = $newFile->translations[0];
        // update english translation
        $translationEn = $this->repository->createTranslation(
            $newFile,
            [
                'langCode'    => 'en',
                'title'       => 'Updated example title',
                'description' => 'Updated example body',
            ]
        );
        // add new polish translation
        $translationPl    = $this->repository->createTranslation(
            $newFile,
            [
                'langCode'    => 'pl',
                'title'       => 'New polish title',
                'description' => 'New polish body',
            ]
        );
        $newTranslationEn = $this->repository->getFileTranslationById($newFile, $translationEn->id);
        $newTranslationPl = $this->repository->getFileTranslationById($newFile, $translationPl->id);
        $this->assertNotSame($file, $newFile);
        $this->assertNotSame($translationEn, $newTranslationEn);
        $this->assertNotSame($translationPl, $newTranslationPl);
        // check if first english translation has been removed
        // file translation
        $foundTranslation = $this->repository->getFileTranslationById($newFile, $firstTranslation->id);
        $this->assertNull($foundTranslation);
        // Check if a new translations has been added
        // English
        $this->assertEquals($translationEn->langCode, $newTranslationEn->langCode);
        $this->assertEquals($translationEn->title, $newTranslationEn->title);
        $this->assertEquals($translationEn->description, $newTranslationEn->description);
        $this->assertEquals($newFile->id, $newTranslationEn->fileId);
        // Polish
        $this->assertEquals($translationPl->langCode, $newTranslationPl->langCode);
        $this->assertEquals($translationPl->title, $newTranslationPl->title);
        $this->assertEquals($translationPl->description, $newTranslationPl->description);
        $this->assertEquals($newFile->id, $newTranslationPl->fileId);
    }

    /**
     * @test
     */
    public function can_set_file_as_inactive()
    {
        $uploadedFile = $this->getExampleFile();
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'langCode'    => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );
        $this->repository->update(
            $file,
            [
                'isActive' => false,
            ]
        );

        $newFile = $this->repository->getById($file->id);

        // File
        $this->assertEquals(0, $newFile->isActive);

    }

    /**
     * @test
     */
    public function can_delete_file()
    {
        $uploadedFile = $this->getExampleFile();
        $file         = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'langCode'    => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );

        $newFile        = $this->repository->getById($file->id);
        $newTranslation = $newFile->translations[0];

        // add new polish translation
        $translationPl = $this->repository->createTranslation(
            $newFile,
            [
                'langCode'    => 'pl',
                'title'       => 'New polish title',
                'description' => 'New polish body',
            ]
        );

        // delete file and all related translations
        $this->repository->delete($newFile);
        // file
        $found = $this->repository->getById($newFile->id);
        $this->assertNull($found);
        // file translations
        $foundTranslation = $this->repository->getFileTranslationById($newFile, $newTranslation->id);
        $this->assertNull($foundTranslation);
        $foundTranslationPl = $this->repository->getFileTranslationById($newFile, $translationPl->id);
        $this->assertNull($foundTranslationPl);
        // file itself
        $this->assertFalse(Storage::exists($newFile->getUploadPath() . $newFile->getFileName()));
    }

    /**
     * @test
     * @expectedException \Gzero\Core\Exception
     * @expectedExceptionMessage File type is invalid
     */
    public function it_checks_existence_of_file_type()
    {
        $uploadedFile = $this->getExampleFile();
        $this->repository->create(
            [
                'type'         => 'fakeType',
                'translations' => [
                    'langCode'    => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
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
        $uploadedFile = $this->getExampleFile();
        // File in header region
        $firstFile = $this->repository->create(
            [
                'type'         => 'image',
                'isActive'     => true,
                'translations' => [
                    'langCode'    => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );


        // File in footer region
        $secondFile = $this->repository->create(
            [
                'type'         => 'document',
                'isActive'     => true,
                'translations' => [
                    'langCode'    => 'en',
                    'title'       => 'Example file title',
                    'description' => 'Example file description'
                ]
            ],
            $uploadedFile
        );

        // Get files
        $files = $this->repository->getFiles(
            [
                ['type', '=', 'image'],
                ['isActive', '=', true]
            ]
        );

        // Check results
        foreach ($files as $file) {
            $this->assertEquals($firstFile->type, $file->type);
            $this->assertNotEquals($secondFile->type, $file->type);
            $this->assertEquals(true, $file->isActive);
        }
    }

    /**
     * @test
     */
    public function can_sort_files_list()
    {
        $uploadedFile = $this->getExampleFile();
        $firstFile    = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'langCode'    => 'en',
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
                    'langCode'    => 'en',
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
                ['createdAt', 'ASC'],
                ['translations.title', 'ASC'],
            ]
        );
        // created at
        $this->assertEquals($firstFile->createdAt, $files[0]['createdAt']);
        // translations title
        $this->assertEquals('A file title', $files[0]['translations'][0]['title']);

        // Descending
        $files = $this->repository->getFiles(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['createdAt', 'DESC'],
                ['translations.title', 'DESC'],
            ]
        );
        // created at
        $this->assertEquals($secondFile->createdAt, $files[0]['createdAt']);
        // translations title
        $this->assertEquals('B file title', $files[0]['translations'][0]['title']);
    }

    /**
     * @test
     */
    public function can_paginate_files_list()
    {
        $uploadedFile = $this->getExampleFile();
        $firstFile    = $this->repository->create(
            [
                'type'         => 'image',
                'translations' => [
                    'langCode'    => 'en',
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
                    'langCode'    => 'en',
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
                ['createdAt', 'ASC'],
            ],
            1, // page
            1 // Items per page
        );

        // First file
        $this->assertEquals(1, count($files)); // Items per page
        $this->assertEquals($firstFile->type, $files[0]->type);
        $this->assertEquals($firstFile['translations'][0]['title'], $files[0]['translations'][0]['title']);
        $this->assertEquals($firstFile['translations'][0]['langCode'], $files[0]['translations'][0]['langCode']);

        // Second Page
        $files = $this->repository->getFiles(
            [],
            [
                ['createdAt', 'ASC'],
            ],
            2, // page
            1 // Items per page
        );
        // Second file
        $this->assertEquals(1, count($files));
        $this->assertEquals($secondFile->type, $files[0]->type);
        $this->assertEquals($secondFile['translations'][0]['title'], $files[0]['translations'][0]['title']);
        $this->assertEquals($secondFile['translations'][0]['langCode'], $files[0]['translations'][0]['langCode']);
    }

    /*
    |--------------------------------------------------------------------------
    | END List tests
    |--------------------------------------------------------------------------
    */

    private function getExampleFile()
    {
        return new UploadedFile($this->filesDir . '/example.png', 'example.png', 'image/jpeg', null, null, true);
    }

}
