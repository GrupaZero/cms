<?php namespace functional;

use Gzero\Entity\Block;
use Gzero\Entity\User;
use Gzero\Entity\File;
use Gzero\Entity\FileType;
use Gzero\Repository\BlockRepository;
use Gzero\Repository\FileRepository;
use Illuminate\Cache\CacheManager;
use Illuminate\Events\Dispatcher;
use Gzero\Core\BlockFinder;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;


require_once(__DIR__ . '/../../stub/TestSeeder.php');
require_once(__DIR__ . '/../../stub/TestTreeSeeder.php');

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockRepositoryTest
 *
 * @package    functional
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class BlockRepositoryTest extends \TestCase  {

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var BlockRepository
     */
    protected $repository;

    /**
     * @var BlockFinder
     */
    protected $finder;

    /**
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * files directory
     */
    protected $filesDir;

    protected function _before()
    {
        // Start the Laravel application
        $this->startApplication();
        $this->fileRepository = new FileRepository(new File(), new FileType(), new Dispatcher());
        $this->repository     = new BlockRepository(new Block(), new Dispatcher(), $this->fileRepository);
        $this->finder         = new BlockFinder($this->repository, new CacheManager($this->app));
        $this->filesDir       = __DIR__ . '/../../resources';
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
     | START Block tests
     |--------------------------------------------------------------------------
     */

    /**
     * @test
     */
    public function can_create_block()
    {
        $author = User::find(1);
        $block  = $this->repository->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'isActive'     => true,
                'isCacheable'  => true,
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ],
            $author
        );

        $newBlock       = $this->repository->getById($block->id);
        $newBlockAuthor = $newBlock->author;
        $newTranslation = $newBlock->translations[0];

        // Block
        $this->assertNotSame($block, $newBlock);
        $this->assertEquals($block->id, $newBlock->id);
        $this->assertEquals($block->type, $newBlock->type);
        $this->assertEquals($block->region, $newBlock->region);
        $this->assertEquals($block->filter, $newBlock->filter);
        $this->assertEquals($block->options, $newBlock->options);
        $this->assertEquals($block->isActive, $newBlock->isActive);
        $this->assertEquals($block->isCacheable, $newBlock->isCacheable);
        // Author
        $this->assertEquals($author->id, $newBlock->authorId);
        $this->assertEquals($author->email, $newBlockAuthor['email']);
        // Translation
        $this->assertEquals($newTranslation->langCode, 'en');
        $this->assertEquals($newTranslation->title, 'Example block title');
    }

    /**
     * @test
     */
    public function can_create_block_type_widget()
    {
        $block    = $this->repository->create(
            [
                'type'         => 'widget',
                'widget'       => [
                    'name'        => 'getLastContent',
                    'args'        => ['contentId' => 1],
                    'isActive'    => 1,
                    'isCacheable' => 1,
                ],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example title'
                ]
            ]
        );
        $newBlock = $this->repository->getById($block->id);
        // Block
        $this->assertNotSame($block, $newBlock);
        $this->assertEquals($block->type, $newBlock->type);
        // Widget
        $this->assertEquals($block->blockable->name, $newBlock->blockable->name);
        $this->assertSame($block->blockable->args, $newBlock->blockable->args);
    }

    /**
     * @test
     */
    public function can_create_block_without_author()
    {
        $block    = $this->repository->create(
            [
                'type'         => 'slider',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example title'
                ]
            ]
        );
        $newBlock = $this->repository->getById($block->id);
        $this->assertNotSame($block, $newBlock);
        $this->assertNull($newBlock->author);
    }

    /**
     * @test
     */
    public function can_create_and_get_block_translation()
    {
        $block            = $this->repository->create(
            [
                'type'         => 'slider',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example title'
                ]
            ]
        );
        $newBlock         = $this->repository->getById($block->id);
        $translation      = $this->repository->createTranslation(
            $newBlock,
            [
                'langCode' => 'en',
                'title'    => 'New example title',
                'body'     => 'New example body',
            ]
        );
        $firstTranslation = $this->repository->getBlockTranslationById($newBlock, 1);
        $newTranslation   = $this->repository->getBlockTranslationById($newBlock, 2);
        $this->assertNotSame($block, $newBlock);
        $this->assertNotSame($translation, $firstTranslation);
        // Check if previous translation are inactive
        $this->assertFalse((bool) $firstTranslation->isActive);
        // Check if a new translation has been added
        $this->assertEquals('en', $newTranslation->langCode);
        $this->assertEquals('New example title', $newTranslation->title);
        $this->assertEquals('New example body', $newTranslation->body);
        $this->assertEquals($newBlock->id, $newTranslation->blockId);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Block type doesn't exist
     */
    public function it_checks_existence_of_block_type()
    {
        $this->repository->create(
            [
                'type'         => 'fakeType',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Widget is required
     */
    public function it_checks_existence_of_widget()
    {
        $this->repository->create(
            [
                'type'         => 'widget',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function can_set_block_filter_as_null()
    {
        $author = User::find(1);
        $block  = $this->repository->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'filter'       => ['+' => ['1/2/3']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ],
            $author
        );
        $this->repository->update(
            $block,
            [
                'filter' => null,
            ],
            $author
        );
        $newBlock = $this->repository->getById($block->id);


        // Block
        $this->assertNull($newBlock->filter);
    }

    /**
     * @test
     */
    public function it_should_force_delete_one_block()
    {
        $author  = User::find(1);
        $block  = $this->repository->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'isActive'     => true,
                'isCacheable'  => true,
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ],
            $author
        );
        $block2  = $this->repository->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'isActive'     => true,
                'isCacheable'  => true,
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ],
            $author
        );

        $this->repository->delete($block);
        $this->repository->delete($block2);

        $this->assertNull($this->repository->getById($block->id));
        $this->assertNull($this->repository->getById($block2->id));

        $this->repository->forceDelete($block);
        $this->assertNull($this->repository->getDeletedById($block->id));

        // Block2 should exist
        $this->assertNotNull($this->repository->getDeletedById($block2->id));
    }

    /**
     * @test
     */
    public function it_should_retrive_non_trashed_block() {
        $block = $this->repository->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'isActive'     => true,
                'isCacheable'  => true,
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );
        $newBlock = $this->repository->getByIdWithTrashed($block->id);
        $this->assertEquals($block->id, $newBlock->id);
    }

    /**
     * @test
     */
    public function it_should_retrive_trashed_block() {
        $block = $this->repository->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'isActive'     => true,
                'isCacheable'  => true,
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );
        $this->repository->delete($block);
        $trashedBlock = $this->repository->getByIdWithTrashed($block->id);
        $this->assertEquals($block->id, $trashedBlock->id);
    }

    /**
     * @test
     */
    public function it_should_not_retrive_force_deleted_block() {
        $block = $this->repository->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'isActive'     => true,
                'isCacheable'  => true,
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );
        $this->repository->forcedelete($block);
        $this->assertNull($this->repository->getByIdWithTrashed($block->id));
    }

    /*
     |--------------------------------------------------------------------------
     | END Block tests
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
    public function can_filter_blocks_list_by_type()
    {
        // Widget type block
        $this->repository->create(
            [
                'type'         => 'widget',
                'isActive'     => 1,
                'widget'       => [
                    'name'        => 'getLastContent',
                    'args'        => ['contentId' => 1],
                    'isActive'    => 1,
                    'isCacheable' => 1,
                ],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );

        // Slider type block
        $this->repository->create(
            [
                'type'         => 'slider',
                'isActive'     => 1,
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );

        // Get widgets block
        $blocks = $this->repository->getBlocks(
            [
                ['type', '=', 'widget'],
                ['isActive', '=', true]
            ]
        );

        // Check results
        foreach ($blocks as $block) {
            $this->assertEquals('widget', $block->type);
            $this->assertNotEquals('slider', $block->type);
            $this->assertEquals(true, $block->isActive);
        }
    }

    /**
     * @test
     */
    public function can_filter_blocks_list_by_region()
    {
        // Block in header region
        $this->repository->create(
            [
                'type'         => 'basic',
                'isActive'     => 1,
                'region'       => 'header',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );

        // Block in footer region
        $this->repository->create(
            [
                'type'         => 'basic',
                'isActive'     => 1,
                'region'       => 'footer',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Example block title'
                ]
            ]
        );

        // Get widgets block
        $blocks = $this->repository->getBlocks(
            [
                ['region', '=', 'header'],
                ['isActive', '=', true]
            ]
        );

        // Check results
        foreach ($blocks as $block) {
            $this->assertEquals('header', $block->region);
            $this->assertNotEquals('footer', $block->type);
            $this->assertEquals(true, $block->isActive);
        }
    }

    /**
     * @test
     */
    public function can_sort_blocks_list()
    {
        // Block in header region
        $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'A title'
                ]
            ]
        );

        // Block in footer region
        $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'isActive'     => 1,
                'region'       => 'footer',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'B title'
                ]
            ]
        );

        // Ascending
        $blocks = $this->repository->getBlocks(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['weight', 'ASC'],
                ['translations.title', 'ASC'],
            ]
        );
        // Weight
        $this->assertEquals(0, $blocks[0]['weight']);
        // Translations title
        $this->assertEquals('A title', $blocks[0]['translations'][0]['title']);

        // Descending
        $blocks = $this->repository->getBlocks(
            [
                ['translations.lang', '=', 'en']
            ],
            [
                ['weight', 'DESC'],
                ['translations.title', 'DESC'],
            ]
        );
        // weight
        $this->assertEquals(1, $blocks[0]['weight']);
        // translations title
        $this->assertEquals('B title', $blocks[0]['translations'][0]['title']);
    }

    /**
     * @test
     */
    public function can_paginate_blocks_list()
    {
        // Block in header region
        $firstBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'A title'
                ]
            ]
        );

        // Block in footer region
        $secondBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'isActive'     => 1,
                'region'       => 'footer',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'B title'
                ]
            ]
        );

        // First Page
        $blocks = $this->repository->getBlocks(
            [],
            [
                ['weight', 'ASC'],
            ],
            1, // Page
            1 // Items per page
        );

        // First block
        $this->assertEquals(1, count($blocks)); // Items per page
        $this->assertEquals($firstBlock->type, $blocks[0]->type);
        $this->assertEquals($firstBlock->region, $blocks[0]->region);
        $this->assertEquals($firstBlock['translations'][0]['title'], $blocks[0]['translations'][0]['title']);
        $this->assertEquals($firstBlock['translations'][0]['langCode'], $blocks[0]['translations'][0]['langCode']);

        // Second Page
        $blocks = $this->repository->getBlocks(
            [],
            [
                ['weight', 'ASC'],
            ],
            2, // Page
            1 // Items per page
        );
        // Second block
        $this->assertEquals(1, count($blocks));
        $this->assertEquals($secondBlock->type, $blocks[0]->type);
        $this->assertEquals($secondBlock->region, $blocks[0]->region);
        $this->assertEquals($secondBlock['translations'][0]['title'], $blocks[0]['translations'][0]['title']);
        $this->assertEquals($secondBlock['translations'][0]['langCode'], $blocks[0]['translations'][0]['langCode']);
    }

    /**
     * @test
     */
    public function can_find_blocks_for_content()
    {
        // Our content path
        $contentPath = '1/2/3/4/5/6/';
        // Block in header region
        $firstBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        // Block in footer region
        $secondBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'isActive'     => 1,
                'region'       => 'footer',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Second block title'
                ]
            ]
        );

        // Block not in this page
        $thirdBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'isActive'     => 1,
                'region'       => 'footer',
                'filter'       => ['+' => ['1/2/3/']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Third block title'
                ]
            ]
        );

        // Block from one of the content parents
        $fourthBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'isActive'     => 1,
                'region'       => 'sidebar',
                'filter'       => ['+' => ['1/2/3/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Fourth block title'
                ]
            ]
        );

        // Block for this specific content
        $fifthBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'isActive'     => 1,
                'region'       => 'sidebar',
                'filter'       => ['+' => ['1/2/3/4/5/6/']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Fifth block title'
                ]
            ]
        );

        // Block shown and hidden on this specific content, should remain hidden
        $sixthBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'isActive'     => 1,
                'region'       => 'footer',
                'filter'       => ['+' => ['1/2/3/4/5/6/'], '-' => ['1/2/3/4/5/6/']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Sixth block title'
                ]
            ]
        );

        // Block shown on all pages
        $seventhBlock = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'isActive'     => 1,
                'region'       => 'header',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'Seventh block title'
                ]
            ]
        );

        $blockIds = $this->finder->getBlocksIds($contentPath);
        $blocks   = $this->repository->getVisibleBlocks($blockIds);

        // Available blocks number
        $this->assertEquals(5, count($blocks));

        // First block
        $this->assertEquals($firstBlock->type, $blocks[0]->type);
        $this->assertEquals($firstBlock->region, $blocks[0]->region);
        $this->assertEquals($firstBlock['translations'][0]['title'], $blocks[0]['translations'][0]['title']);
        $this->assertEquals($firstBlock['translations'][0]['langCode'], $blocks[0]['translations'][0]['langCode']);
        // Second block
        $this->assertEquals($secondBlock->type, $blocks[1]->type);
        $this->assertEquals($secondBlock->region, $blocks[1]->region);
        $this->assertEquals($secondBlock['translations'][0]['title'], $blocks[1]['translations'][0]['title']);
        $this->assertEquals($secondBlock['translations'][0]['langCode'], $blocks[1]['translations'][0]['langCode']);
        // Fourth block
        $this->assertEquals($fourthBlock->type, $blocks[2]->type);
        $this->assertEquals($fourthBlock->region, $blocks[2]->region);
        $this->assertEquals($fourthBlock['translations'][0]['title'], $blocks[2]['translations'][0]['title']);
        $this->assertEquals($fourthBlock['translations'][0]['langCode'], $blocks[2]['translations'][0]['langCode']);
        // Fifth block
        $this->assertEquals($fifthBlock->type, $blocks[3]->type);
        $this->assertEquals($fifthBlock->region, $blocks[3]->region);
        $this->assertEquals($fifthBlock['translations'][0]['title'], $blocks[3]['translations'][0]['title']);
        $this->assertEquals($fifthBlock['translations'][0]['langCode'], $blocks[3]['translations'][0]['langCode']);
        // Seventh block
        $this->assertEquals($seventhBlock->type, $blocks[4]->type);
        $this->assertEquals($seventhBlock->region, $blocks[4]->region);
        $this->assertEquals($seventhBlock['translations'][0]['title'], $blocks[4]['translations'][0]['title']);
        $this->assertEquals($seventhBlock['translations'][0]['langCode'], $blocks[4]['translations'][0]['langCode']);
    }

    /*
    |--------------------------------------------------------------------------
    | END List tests
    |--------------------------------------------------------------------------
    */

    /*
   |--------------------------------------------------------------------------
   | START Files tests
   |--------------------------------------------------------------------------
   */

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryValidationException
     * @expectedExceptionMessage File (id: 1) does not exist
     */
    public function it_checks_existence_of_files_to_add()
    {
        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        $this->repository->addFiles($block, [1]);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryValidationException
     * @expectedExceptionMessage You must provide the files in order to add them to the block
     */
    public function it_checks_for_empty_array_of_files_to_add()
    {
        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        $this->repository->addFiles($block, []);
    }

    /**
     * @test
     */
    public function can_get_single_block_file()
    {
        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        // Create files
        $uploadedFile = $this->getExampleImage();
        $author       = User::find(1);
        $file         = $this->fileRepository->create(
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

        // Assign files
        $this->repository->addFiles($block, [$file->id]);
        $relatedFile   = $this->repository->getBlockFileById($block, $file->id);

        $this->assertNotEmpty($relatedFile);
        $this->assertEquals($file->id, $relatedFile->id);
        $this->assertEquals($file->name, $relatedFile->name);
        $this->assertEquals($file->type, $relatedFile->type);
        $this->assertEquals($file->isActive, $relatedFile->isActive);
        $this->assertEquals($file->extension, $relatedFile->extension);
        $this->assertEquals($file->mimeType, $relatedFile->mimeType);
        $this->assertEquals($file->info, $relatedFile->info);
        $this->assertEquals($file->translations[0]->langCode, $relatedFile->translations[0]->langCode);
        $this->assertEquals($file->translations[0]->title, $relatedFile->translations[0]->title);
        $this->assertEquals($file->translations[0]->description, $relatedFile->translations[0]->description);
    }

    /**
     * @test
     */
    public function can_add_block_files()
    {
        $fileIds = [];

        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        // Create files
        $uploadedFile = $this->getExampleImage();
        $author       = User::find(1);
        for ($i = 0; $i < 3; $i++) {
            $file = $this->fileRepository->create(
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

            $fileIds[] = $file->id;
        }

        // Assign files
        $this->repository->addFiles($block, $fileIds);
        $files = $block->files()->get();

        $this->assertNotEmpty($files);
        $this->assertEquals('example', $files[0]->name);
        $this->assertEquals('example-1', $files[1]->name);
        $this->assertEquals('example-2', $files[2]->name);

        foreach ($files as $index => $file) {
            $this->assertEquals($fileIds[$index], $file->id);
            $this->assertEquals('image', $file->type);
            $this->assertEquals(true, $file->isActive);
            $this->assertEquals('png', $file->extension);
            $this->assertEquals('image/png', $file->mimeType);
            $this->assertEquals(['key' => 'value'], $file->info);
            $this->assertEquals('en', $file->translations[0]->langCode);
            $this->assertEquals('Example file title', $file->translations[0]->title);
            $this->assertEquals('Example file description', $file->translations[0]->description);
        }
    }


    /**
     * @test
     */
    public function can_sort_block_files_list()
    {
        $fileIds = [];

        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        // Create files
        $uploadedFile = $this->getExampleImage();
        $author       = User::find(1);
        for ($i = 0; $i < 3; $i++) {
            $file = $this->fileRepository->create(
                [
                    'type'         => 'image',
                    'isActive'     => true,
                    'info'         => ['key' => 'value'],
                    'translations' => [
                        'langCode'    => 'en',
                        'title'       => 'Example file title ' . $i,
                        'description' => 'Example file description'
                    ]
                ],
                $uploadedFile,
                $author
            );

            $fileIds[] = $file->id;
        }

        // Assign files
        $this->repository->addFiles($block, $fileIds);
        $files = $this->repository->getFiles(
            $block,
            [['translations.lang', '=', 'en']],
            [['translations.title', 'ASC']]
        );

        $this->assertNotEmpty($files);
        $this->assertEquals('example', $files[0]->name);
        $this->assertEquals('Example file title 0', $files[0]->translations[0]->title);
        $this->assertEquals('example-1', $files[1]->name);
        $this->assertEquals('Example file title 1', $files[1]->translations[0]->title);
        $this->assertEquals('example-2', $files[2]->name);
        $this->assertEquals('Example file title 2', $files[2]->translations[0]->title);
    }

    /**
     * @test
     */
    public function can_filter_block_files_list()
    {

        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        // Create files
        $uploadedFile = $this->getExampleImage();
        $author       = User::find(1);
        $file = $this->fileRepository->create(
            [
                'type'         => 'image',
                'isActive'     => false,
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

        // Assign files
        $this->repository->addFiles($block, [$file->id]);
        $files = $this->repository->getFiles(
            $block,
            [['isActive', '=', true]],
            []
        );

        $this->assertEmpty($files);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryValidationException
     * @expectedExceptionMessage You must provide the file in order to update it
     */
    public function it_checks_for_empty_file_id_to_update()
    {
        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        $this->repository->updateFile($block, null, []);
    }

    /**
     * @test
     */
    public function can_update_block_file()
    {
        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        // Create files
        $uploadedFile = $this->getExampleImage();
        $author       = User::find(1);
        $file = $this->fileRepository->create(
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

        $this->repository->addFiles($block, [$file->id]);
        $this->repository->updateFile($block, $file->id, ['weight' => 2]);
        $files = $this->repository->getFiles($block, [], []);

        $this->assertNotEmpty($files);
        $this->assertEquals(2, $files[0]->pivot->weight);
    }


    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryValidationException
     * @expectedExceptionMessage You must provide the files in order to remove them from the block
     */
    public function it_checks_for_empty_array_of_files_to_remove()
    {
        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );

        $this->repository->removeFiles($block, []);
    }

    /**
     * @test
     */
    public function can_remove_block_files()
    {
        $fileIds = [];

        // Create new block with first translation
        $block = $this->repository->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'isActive'     => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'First block title'
                ]
            ]
        );
        // Create files
        $uploadedFile = $this->getExampleImage();
        $author       = User::find(1);
        for ($i = 0; $i < 3; $i++) {
            $file = $this->fileRepository->create(
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

            $fileIds[] = $file->id;
        }

        $this->repository->addFiles($block, $fileIds);
        $this->repository->removeFiles($block, $fileIds);
        $files = $block->files()->get();

        $this->assertEmpty($files);
    }

    /*
    |--------------------------------------------------------------------------
    | END Files tests
    |--------------------------------------------------------------------------
    */

    private function getExampleImage()
    {
        return new UploadedFile($this->filesDir . '/example.png', 'example.png', 'image/jpeg', null, null, true);
    }

}
