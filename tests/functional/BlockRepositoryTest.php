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

}