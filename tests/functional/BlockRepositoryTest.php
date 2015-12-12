<?php namespace functional;

use Gzero\Entity\Block;
use Gzero\Entity\User;
use Gzero\Repository\BlockRepository;
use Illuminate\Events\Dispatcher;

require_once(__DIR__ . '/../stub/TestSeeder.php');
require_once(__DIR__ . '/../stub/TestTreeSeeder.php');

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
class BlockRepositoryTest extends \EloquentTestCase {

    /**
     * @var BlockRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new BlockRepository(new Block(), new Dispatcher());
        $this->seed('TestSeeder'); // Relative to tests/app/
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
     * @expectedException \Gzero\Core\Exception
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
     * @expectedException \Gzero\Core\Exception
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
        // weight
        $this->assertEquals(0, $blocks[0]['weight']);
        // translations title
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
            1, // page
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
            2, // page
            1 // Items per page
        );
        // Second block
        $this->assertEquals(1, count($blocks));
        $this->assertEquals($secondBlock->type, $blocks[0]->type);
        $this->assertEquals($secondBlock->region, $blocks[0]->region);
        $this->assertEquals($secondBlock['translations'][0]['title'], $blocks[0]['translations'][0]['title']);
        $this->assertEquals($secondBlock['translations'][0]['langCode'], $blocks[0]['translations'][0]['langCode']);
    }

    /*
    |--------------------------------------------------------------------------
    | END List tests
    |--------------------------------------------------------------------------
    */

}
