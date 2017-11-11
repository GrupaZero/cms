<?php namespace Cms;

use Gzero\Base\Models\User;
use Gzero\Cms\Service\BlockService;
use Illuminate\Cache\CacheManager;
use Gzero\Core\BlockFinder;

class BlockServiceTest extends \Codeception\Test\Unit {

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var BlockService
     */
    protected $service;

    /**
     * @var BlockFinder
     */
    protected $finder;

    /**
     * files directory
     */
    protected $filesDir;

    protected function _before()
    {
        $this->service  = new BlockService();
        $this->finder   = new BlockFinder($this->service, new CacheManager(app()));
        $this->filesDir = __DIR__ . '/../../resources';
    }

    /** @test */
    public function canCreateBlock()
    {
        $author = User::find(1);

        $block = $this->service->create(['is_active' => true], [
            'pl' => [],
            'de' => []
        ], $author);

        $block  = $this->service->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    'en' => ['title' => 'Example block title', 'url' => 'asdasfasfasfas']
                ]
            ],
            $author
        );

        $newBlock       = $this->service->getById($block->id);
        $newBlockAuthor = $newBlock->author;
        $newTranslation = $newBlock->translations[0];

        // Block
        $this->assertNotSame($block, $newBlock);
        $this->assertEquals($block->id, $newBlock->id);
        $this->assertEquals($block->type, $newBlock->type);
        $this->assertEquals($block->region, $newBlock->region);
        $this->assertEquals($block->filter, $newBlock->filter);
        $this->assertEquals($block->options, $newBlock->options);
        $this->assertEquals($block->is_active, $newBlock->is_active);
        $this->assertEquals($block->is_cacheable, $newBlock->is_cacheable);
        // Author
        $this->assertEquals($author->id, $newBlock->author_id);
        $this->assertEquals($author->email, $newBlockAuthor['email']);
        // Translation
        $this->assertEquals($newTranslation->language_code, 'en');
        $this->assertEquals($newTranslation->title, 'Example block title');
    }

    /** @test */
    public function canCreateBlockTypeWidget()
    {
        $block    = $this->service->create(
            [
                'type'         => 'widget',
                'widget'       => [
                    'name'         => 'getLastContent',
                    'args'         => ['content_id' => 1],
                    'is_active'    => 1,
                    'is_cacheable' => 1,
                ],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example title'
                ]
            ]
        );
        $newBlock = $this->service->getById($block->id);
        // Block
        $this->assertNotSame($block, $newBlock);
        $this->assertEquals($block->type, $newBlock->type);
        // Widget
        $this->assertEquals($block->blockable->name, $newBlock->blockable->name);
        $this->assertSame($block->blockable->args, $newBlock->blockable->args);
    }

    /** @test */
    public function canCreateBlockWithoutAuthor()
    {
        $block    = $this->service->create(
            [
                'type'         => 'slider',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example title'
                ]
            ]
        );
        $newBlock = $this->service->getById($block->id);
        $this->assertNotSame($block, $newBlock);
        $this->assertNull($newBlock->author);
    }

    /** @test */
    public function canCreateAndGetBlockTranslation()
    {
        $block            = $this->service->create(
            [
                'type'         => 'slider',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example title'
                ]
            ]
        );
        $old              = $block->translations()->first();
        $new              = $this->service->createTranslation(
            $block,
            [
                'language_code' => 'en',
                'title'         => 'New example title',
                'body'          => 'New example body',
            ]
        );
        $firstTranslation = $this->service->getBlockTranslationById($block, $old->id);
        $newTranslation   = $this->service->getBlockTranslationById($block, $new->id);
        // Check if previous translation are inactive
        $this->assertFalse((bool) $firstTranslation->is_active);
        // Check if a new translation has been added
        $this->assertEquals('en', $newTranslation->language_code);
        $this->assertEquals('New example title', $newTranslation->title);
        $this->assertEquals('New example body', $newTranslation->body);
        $this->assertEquals($block->id, $newTranslation->block_id);
    }

    /**
     * @test
     * @expectedException \Gzero\Base\Service\RepositoryException
     * @expectedExceptionMessage Block type doesn't exist
     */
    public function itChecksExistenceOfBlockType()
    {
        $this->service->create(
            [
                'type'         => 'fakeType',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );
    }

    /**
     * @test
     * @expectedException \Gzero\Base\Service\RepositoryException
     * @expectedExceptionMessage Widget is required
     */
    public function itChecksExistenceOfWidget()
    {
        $this->service->create(
            [
                'type'         => 'widget',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );
    }

    /** @test */
    public function canSetBlockFilterAsNull()
    {
        $author = User::find(1);
        $block  = $this->service->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'filter'       => ['+' => ['1/2/3']],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ],
            $author
        );
        $this->service->update(
            $block,
            [
                'filter' => null,
            ],
            $author
        );
        $newBlock = $this->service->getById($block->id);

        // Block
        $this->assertNull($newBlock->filter);
    }

    /** @test */
    public function itShouldForceDeleteOneBlock()
    {
        $author = User::find(1);
        $block  = $this->service->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ],
            $author
        );
        $block2 = $this->service->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ],
            $author
        );

        $this->service->delete($block);
        $this->service->delete($block2);

        $this->assertNull($this->service->getById($block->id));
        $this->assertNull($this->service->getById($block2->id));

        $this->service->forceDelete($block);
        $this->assertNull($this->service->getDeletedById($block->id));

        // Block2 should exist
        $this->assertNotNull($this->service->getDeletedById($block2->id));
    }

    /** @test */
    public function itShouldRetrieveNonTrashedBlock()
    {
        $block    = $this->service->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );
        $newBlock = $this->service->getByIdWithTrashed($block->id);
        $this->assertEquals($block->id, $newBlock->id);
    }

    /** @test */
    public function itShouldRetrieveTrashedBlock()
    {
        $block = $this->service->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );
        $this->service->delete($block);
        $trashedBlock = $this->service->getByIdWithTrashed($block->id);
        $this->assertEquals($block->id, $trashedBlock->id);
    }

    /** @test */
    public function itShouldNotRetrieveForceDeletedBlock()
    {
        $block = $this->service->create(
            [
                'type'         => 'menu',
                'region'       => 'test',
                'weight'       => 1,
                'filter'       => ['+' => ['1/2/3']],
                'options'      => ['test' => 'value'],
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );
        $this->service->forcedelete($block);
        $this->assertNull($this->service->getByIdWithTrashed($block->id));
    }

    /** @test */
    public function canFilterBlocksListByType()
    {
        // Widget type block
        $this->service->create(
            [
                'type'         => 'widget',
                'is_active'    => 1,
                'widget'       => [
                    'name'         => 'getLastContent',
                    'args'         => ['content_id' => 1],
                    'is_active'    => 1,
                    'is_cacheable' => 1,
                ],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );

        // Slider type block
        $this->service->create(
            [
                'type'         => 'slider',
                'is_active'    => 1,
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );

        // Get widgets block
        $blocks = $this->service->getBlocks(
            [
                ['type', '=', 'widget'],
                ['is_active', '=', true]
            ]
        );

        // Check results
        foreach ($blocks as $block) {
            $this->assertEquals('widget', $block->type);
            $this->assertNotEquals('slider', $block->type);
            $this->assertEquals(true, $block->is_active);
        }
    }

    /** @test */
    public function canFilterBlocksListByRegion()
    {
        // Block in header region
        $this->service->create(
            [
                'type'         => 'basic',
                'is_active'    => 1,
                'region'       => 'header',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );

        // Block in footer region
        $this->service->create(
            [
                'type'         => 'basic',
                'is_active'    => 1,
                'region'       => 'footer',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Example block title'
                ]
            ]
        );

        // Get widgets block
        $blocks = $this->service->getBlocks(
            [
                ['region', '=', 'header'],
                ['is_active', '=', true]
            ]
        );

        // Check results
        foreach ($blocks as $block) {
            $this->assertEquals('header', $block->region);
            $this->assertNotEquals('footer', $block->type);
            $this->assertEquals(true, $block->is_active);
        }
    }

    /** @test */
    public function canSortBlocksList()
    {
        // Block in header region
        $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'is_active'    => 1,
                'region'       => 'header',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'A title'
                ]
            ]
        );

        // Block in footer region
        $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'is_active'    => 1,
                'region'       => 'footer',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'B title'
                ]
            ]
        );

        // Ascending
        $blocks = $this->service->getBlocks(
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
        $blocks = $this->service->getBlocks(
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

    /** @test */
    public function canPaginateBlocksList()
    {
        // Block in header region
        $firstBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'is_active'    => 1,
                'region'       => 'header',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'A title'
                ]
            ]
        );

        // Block in footer region
        $secondBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'is_active'    => 1,
                'region'       => 'footer',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'B title'
                ]
            ]
        );

        // First Page
        $blocks = $this->service->getBlocks(
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
        $this->assertEquals($firstBlock['translations'][0]['language_code'], $blocks[0]['translations'][0]['language_code']);

        // Second Page
        $blocks = $this->service->getBlocks(
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
        $this->assertEquals($secondBlock['translations'][0]['language_code'], $blocks[0]['translations'][0]['language_code']);
    }

    /** @test */
    public function canFindBlocksForContent()
    {
        // Our content path
        $contentPath = '1/2/3/4/5/6/';
        // Block in header region
        $firstBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 0,
                'is_active'    => 1,
                'region'       => 'header',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'First block title'
                ]
            ]
        );

        // Block in footer region
        $secondBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'is_active'    => 1,
                'region'       => 'footer',
                'filter'       => ['+' => ['1/*']],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Second block title'
                ]
            ]
        );

        // Block not in this page
        $thirdBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'is_active'    => 1,
                'region'       => 'footer',
                'filter'       => ['+' => ['1/2/3/']],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Third block title'
                ]
            ]
        );

        // Block from one of the content parents
        $fourthBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'is_active'    => 1,
                'region'       => 'sidebar',
                'filter'       => ['+' => ['1/2/3/*']],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Fourth block title'
                ]
            ]
        );

        // Block for this specific content
        $fifthBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'is_active'    => 1,
                'region'       => 'sidebar',
                'filter'       => ['+' => ['1/2/3/4/5/6/']],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Fifth block title'
                ]
            ]
        );

        // Block shown and hidden on this specific content, should remain hidden
        $sixthBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'is_active'    => 1,
                'region'       => 'footer',
                'filter'       => ['+' => ['1/2/3/4/5/6/'], '-' => ['1/2/3/4/5/6/']],
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Sixth block title'
                ]
            ]
        );

        // Block shown on all pages
        $seventhBlock = $this->service->create(
            [
                'type'         => 'basic',
                'weight'       => 1,
                'is_active'    => 1,
                'region'       => 'header',
                'translations' => [
                    'language_code' => 'en',
                    'title'         => 'Seventh block title'
                ]
            ]
        );

        $blockIds = $this->finder->getBlocksIds($contentPath);
        $blocks   = $this->service->getVisibleBlocks($blockIds);

        // Available blocks number
        $this->assertEquals(5, count($blocks));

        // First block
        $this->assertEquals($firstBlock->type, $blocks[0]->type);
        $this->assertEquals($firstBlock->region, $blocks[0]->region);
        $this->assertEquals($firstBlock['translations'][0]['title'], $blocks[0]['translations'][0]['title']);
        $this->assertEquals($firstBlock['translations'][0]['language_code'], $blocks[0]['translations'][0]['language_code']);
        // Second block
        $this->assertEquals($secondBlock->type, $blocks[1]->type);
        $this->assertEquals($secondBlock->region, $blocks[1]->region);
        $this->assertEquals($secondBlock['translations'][0]['title'], $blocks[1]['translations'][0]['title']);
        $this->assertEquals($secondBlock['translations'][0]['language_code'], $blocks[1]['translations'][0]['language_code']);
        // Fourth block
        $this->assertEquals($fourthBlock->type, $blocks[2]->type);
        $this->assertEquals($fourthBlock->region, $blocks[2]->region);
        $this->assertEquals($fourthBlock['translations'][0]['title'], $blocks[2]['translations'][0]['title']);
        $this->assertEquals($fourthBlock['translations'][0]['language_code'], $blocks[2]['translations'][0]['language_code']);
        // Fifth block
        $this->assertEquals($fifthBlock->type, $blocks[3]->type);
        $this->assertEquals($fifthBlock->region, $blocks[3]->region);
        $this->assertEquals($fifthBlock['translations'][0]['title'], $blocks[3]['translations'][0]['title']);
        $this->assertEquals($fifthBlock['translations'][0]['language_code'], $blocks[3]['translations'][0]['language_code']);
        // Seventh block
        $this->assertEquals($seventhBlock->type, $blocks[4]->type);
        $this->assertEquals($seventhBlock->region, $blocks[4]->region);
        $this->assertEquals($seventhBlock['translations'][0]['title'], $blocks[4]['translations'][0]['title']);
        $this->assertEquals($seventhBlock['translations'][0]['language_code'], $blocks[4]['translations'][0]['language_code']);
    }

}
