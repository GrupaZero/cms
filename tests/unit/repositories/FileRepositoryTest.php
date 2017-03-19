<?php namespace functional;

use Gzero\Entity\Block;
use Gzero\Entity\Content;
use Gzero\Entity\File;
use Gzero\Entity\FileTranslation;
use Gzero\Entity\FileType;
use Gzero\Entity\User;
use Gzero\Repository\FileRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Mockery as m;
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

    /**
     * @var m\MockInterface
     */
    protected $diskMock;


    protected function _before()
    {
        $this->diskMock    = m::mock(Filesystem::class);
        $filesystemManager = m::mock(FilesystemManager::class)->shouldReceive('disk')->andReturn($this->diskMock)->getMock();
        // Start the Laravel application
        $this->startApplication();
        $this->repository = new FileRepository(new File(), new FileType(), new Dispatcher(), $filesystemManager);
        $this->filesDir   = __DIR__ . '/../../resources';
        $this->seed('TestSeeder'); // Relative to tests/app/
    }

    protected function _after()
    {
        m::close();
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
        $this->diskMock->shouldReceive('has')->once()->andReturn(false);
        $this->diskMock->shouldReceive('putFileAs')->once()->withArgs(['images/', $uploadedFile, 'example.png']);

        $author = User::find(1);
        $file   = $this->repository->create(
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
        $this->diskMock->shouldReceive('has')->once()->andReturn(false);
        $this->diskMock->shouldReceive('putFileAs')->once()->withArgs(['images/', $uploadedFile, 'example.png']);

        $file    = $this->repository->create(
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
        $newFile = $this->repository->getById($file->id);
        $this->assertNotSame($file, $newFile);
        $this->assertEquals($file->name, $newFile->name);
    }

    /**
     * @test
     */
    public function can_create_file_without_translation()
    {
        $uploadedFile = $this->getExampleImage();
        $this->diskMock->shouldReceive('has')->once()->andReturn(false);
        $this->diskMock->shouldReceive('putFileAs')->once()->withArgs(['images/', $uploadedFile, 'example.png']);

        $file    = $this->repository->create(
            [
                'type'      => 'image',
                'is_active' => true
            ],
            $uploadedFile
        );
        $newFile = $this->repository->getById($file->id);
        $this->assertNotSame($file, $newFile);
        $this->assertEquals($file->name, $newFile->name);
    }

    /**
     * @test
     */
    public function can_create_file_with_unique_name_if_file_name_is_already_taken()
    {
        $uploadedFile = $this->getExampleImage();
        $this->diskMock->shouldReceive('has')->andReturn(true);
        $this->diskMock->shouldReceive('putFileAs')
            ->withArgs(
                [
                    'images/',
                    $uploadedFile,
                    m::on(
                        function ($fileName) {
                            $this->assertRegExp('/^example_.+\.png$/', $fileName);
                            return true;
                        }
                    )
                ]
            )
            ->andReturn(true)
            ->getMock();

        $file = $this->repository->create(
            [
                'type'      => 'image',
                'is_active' => true
            ],
            $uploadedFile
        );

        $this->assertNotEmpty($file);
    }

    /**
     * @test
     */
    public function can_create_and_get_file_translation()
    {
        $file = File::create(
            [
                'type'      => 'image',
                'is_active' => true
            ]
        );

        $translation = new FileTranslation();
        $translation->fill(
            [
                'lang_code'   => 'en',
                'title'       => 'Example file title',
                'description' => 'New example body',
            ]
        );
        $file->translations()->save($translation);

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
        $newTranslationEn = $this->repository->getTranslationById($newFile, $translationEn->id);
        $newTranslationPl = $this->repository->getTranslationById($newFile, $translationPl->id);

        // Check if first english translation has been removed. No history for files
        $foundTranslation = $this->repository->getTranslationById($newFile, $firstTranslation->id);
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
        $file = File::create(
            [
                'type'      => 'image',
                'is_active' => true
            ]
        );

        $this->repository->update(
            $file,
            [
                'is_active' => false,
            ]
        );

        $newFile = $this->repository->getById($file->id);

        // File
        $this->assertFalse($newFile->is_active);

    }

    /**
     * @test
     */
    public function can_delete_file()
    {
        $this->diskMock->shouldReceive('has')->once()->andReturn(true);
        $this->diskMock->shouldReceive('delete')->once()->withArgs(['images/example.png']);

        $file = File::create(
            [
                'type'      => 'image',
                'name'      => 'example',
                'extension' => 'png',
            ]
        );

        $translation = new FileTranslation();
        $translation->fill(
            [
                'lang_code'   => 'pl',
                'title'       => 'New polish title',
                'description' => 'New polish body',
            ]
        );
        $file->translations()->save($translation);

        $newFile        = $this->repository->getById($file->id);
        $newTranslation = $newFile->translations[0];

        // Delete file and all related translations
        $this->repository->delete($newFile);

        $found            = $this->repository->getById($newFile->id);
        $foundTranslation = $this->repository->getTranslationById($newFile, $newTranslation->id);

        $this->assertNull($found);
        $this->assertNull($foundTranslation);
    }

    /**
     * @test
     */
    public function it_should_delete_only_file_in_db_if_no_file_on_disk()
    {
        $this->diskMock->shouldReceive('has')->once()->andReturn(false);
        $this->diskMock->shouldNotReceive('delete');

        $file = File::create(
            [
                'type'      => 'image',
                'name'      => 'example',
                'extension' => 'png',
            ]
        );

        $translation = new FileTranslation();
        $translation->fill(
            [
                'lang_code'   => 'pl',
                'title'       => 'New polish title',
                'description' => 'New polish body',
            ]
        );
        $file->translations()->save($translation);

        $newFile        = $this->repository->getById($file->id);
        $newTranslation = $newFile->translations[0];

        // Delete file and all related translations
        $this->repository->delete($newFile);

        $found            = $this->repository->getById($newFile->id);
        $foundTranslation = $this->repository->getTranslationById($newFile, $newTranslation->id);

        $this->assertNull($found);
        $this->assertNull($foundTranslation);
    }

    /**
     * @test
     */
    public function can_delete_file_translation()
    {
        $file = File::create(
            [
                'type'      => 'image',
                'name'      => 'example',
                'extension' => 'png',
            ]
        );

        $translation = new FileTranslation();
        $translation->fill(
            [
                'lang_code'   => 'pl',
                'title'       => 'New polish title',
                'description' => 'New polish body',
            ]
        );
        $file->translations()->save($translation);

        $newFile        = $this->repository->getById($file->id);
        $newTranslation = $newFile->translations[0];

        // Delete file translation
        $this->repository->deleteTranslation($newTranslation);
        // File translations
        $foundTranslation = $this->repository->getTranslationById($newFile, $newTranslation->id);
        $this->assertNull($foundTranslation);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage File type is invalid
     */
    public function it_checks_existence_of_file_type()
    {
        $this->diskMock->shouldNotHaveReceived('has');
        $this->diskMock->shouldNotHaveReceived('putFileAs');

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
        $this->diskMock->shouldNotHaveReceived('has');
        $this->diskMock->shouldNotHaveReceived('putFileAs');

        $uploadedFile = $this->getExampleImage();
        $this->repository->create(
            [
                'type' => 'video'
            ],
            $uploadedFile
        );
    }

    /**
     * @test
     */
    public function it_should_sync_files_with_content()
    {
        $content = Content::create(['type' => 'content']);
        $file1   = File::create(
            [
                'type'      => 'image',
                'name'      => 'example',
                'extension' => 'png',
            ]
        );
        $file2   = File::create(
            [
                'type'      => 'image',
                'name'      => 'example2',
                'extension' => 'png',
            ]
        );

        $response = $this->repository->syncWith($content, [$file1->id, $file2->id]);
        $this->assertEquals(
            [
                "attached" => [
                    $file1->id,
                    $file2->id,
                ],
                "detached" => [],
                "updated"  => []
            ],
            $response
        );
    }

    /**
     * @test
     */
    public function it_should_sync_files_with_Block()
    {
        $block = Block::create(['type' => 'basic']);
        $file1 = File::create(
            [
                'type'      => 'image',
                'name'      => 'example',
                'extension' => 'png',
            ]
        );
        $file2 = File::create(
            [
                'type'      => 'image',
                'name'      => 'example2',
                'extension' => 'png',
            ]
        );

        $response = $this->repository->syncWith($block, [$file1->id, $file2->id]);
        $this->assertEquals(
            [
                "attached" => [
                    $file1->id,
                    $file2->id
                ],
                "detached" => [],
                "updated"  => []
            ],
            $response
        );
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryValidationException
     * @expectedExceptionMessage File ids [2, 3, 70, 22] does not exist
     */
    public function it_checks_existence_of_file_during_sync()
    {
        $content = Content::create(['type' => 'content']);
        $file1   = File::create(
            [
                'type'      => 'image',
                'name'      => 'example',
                'extension' => 'png',
            ]
        );

        $this->repository->syncWith($content, [$file1->id, 2, 3, 70, 22]);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryValidationException
     * @expectedExceptionMessage Entity does not exist
     */
    public function it_checks_existence_of_content_during_sync()
    {
        $content = new Content(['type' => 'content']);
        $file1   = File::create(
            [
                'type'      => 'image',
                'name'      => 'example',
                'extension' => 'png',
            ]
        );

        $this->repository->syncWith($content, [$file1->id]);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryValidationException
     * @expectedExceptionMessage Entity does not exist
     */
    public function it_checks_existence_of_block_during_sync()
    {
        $block = new Block(['type' => 'basic']);
        $file1   = File::create(
            [
                'type'      => 'image',
                'name'      => 'example',
                'extension' => 'png',
            ]
        );

        $this->repository->syncWith($block, [$file1->id]);
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
        // Image file
        $firstFile = File::create(
            [
                'type'      => 'image',
                'is_active' => true
            ]
        );

        // Document file
        $secondFile = File::create(
            [
                'type'      => 'document',
                'is_active' => true
            ]
        );

        // Get files
        $files = $this->repository->getFiles(
            [
                ['type', '=', 'image'],
                ['is_active', '=', true]
            ]
        );

        $files->each(
            function ($file) use ($firstFile, $secondFile) {
                $this->assertEquals($firstFile->type, $file->type);
                $this->assertNotEquals($secondFile->type, $file->type);
                $this->assertEquals(true, $file->is_active);
            }
        );
    }

    /**
     * @test
     */
    public function can_sort_files_list()
    {
        $firstFile  = File::create(
            [
                'type' => 'image',
            ]
        );
        $secondFile = File::create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'B file title',
                    'description' => 'B file description'
                ]
            ]
        );

        $firstFileTranslation = new FileTranslation();
        $firstFileTranslation->fill(
            [
                'lang_code'   => 'en',
                'title'       => 'A file title',
                'description' => 'A file description'
            ]
        );

        $secondFileTranslation = new FileTranslation();
        $secondFileTranslation->fill(
            [
                'lang_code'   => 'en',
                'title'       => 'B file title',
                'description' => 'B file description'
            ]
        );

        $firstFile->translations()->save($firstFileTranslation);
        $secondFile->translations()->save($secondFileTranslation);

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
        $firstFile  = File::create(
            [
                'type' => 'image',
            ]
        );
        $secondFile = File::create(
            [
                'type'         => 'image',
                'translations' => [
                    'lang_code'   => 'en',
                    'title'       => 'B file title',
                    'description' => 'B file description'
                ]
            ]
        );

        $firstFileTranslation = new FileTranslation();
        $firstFileTranslation->fill(
            [
                'lang_code'   => 'en',
                'title'       => 'A file title',
                'description' => 'A file description'
            ]
        );
        $secondFileTranslation = new FileTranslation();
        $secondFileTranslation->fill(
            [
                'lang_code'   => 'en',
                'title'       => 'B file title',
                'description' => 'B file description'
            ]
        );

        $firstFile->translations()->save($firstFileTranslation);
        $secondFile->translations()->save($secondFileTranslation);

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
}
