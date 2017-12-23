<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Jobs\AddBlockTranslation;
use Gzero\Cms\Jobs\CreateBlock;
use Gzero\Cms\Jobs\DeleteBlock;
use Gzero\Cms\Jobs\DeleteBlockTranslation;
use Gzero\Cms\Jobs\UpdateBlock;
use Gzero\Cms\Models\Block;
use Gzero\Core\Models\Language;
use Gzero\DomainException;
use Gzero\InvalidArgumentException;

class BlockJobsTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function canCreateBlock()
    {
        $user  = $this->tester->haveUser();
        $block = dispatch_now(CreateBlock::basic('New One', new Language(['code' => 'en']), $user, [
            'region'        => 'region',
            'theme'         => 'theme',
            'weight'        => 10,
            'filter'        => 'filter',
            'options'       => 'options',
            'body'          => 'Body',
            'custom_fields' => 'custom fields',
            'is_active'     => true,
            'is_cacheable'  => true
        ]));

        $block       = $block->fresh();
        $translation = $block->translations->first();

        $this->assertTrue($block->is_active);
        $this->assertTrue($block->is_cacheable);
        $this->assertEquals(10, $block->weight);
        $this->assertEquals('region', $block->region);
        $this->assertEquals('theme', $block->theme);
        $this->assertEquals('filter', $block->filter);
        $this->assertEquals('options', $block->options);
        $this->assertEquals($user->id, $block->author->id, 'Author was set');

        $this->assertEquals('basic', $block->type->name, 'Correct content type was set');

        $this->assertEquals('New One', $translation->title, 'Title was set');
        $this->assertEquals('Body', $translation->body, 'Body was set');
        $this->assertEquals('custom fields', $translation->custom_fields);
        $this->assertEquals('en', $translation->language_code, 'Language code was set');
    }

    /** @test */
    public function itValidatesBlockType()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);

        try {
            dispatch_now(CreateBlock::make('title', $language, $user, ['type' => 'component']));
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Unknown block type', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function canAddBlockTranslation()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);
        $block    = $this->tester->haveBlock();

        $this->assertEquals(0, $block->translations()->count());

        $translation = dispatch_now(new AddBlockTranslation($block, 'New example', $language, $user,
            [
                'body'          => 'Body',
                'custom_fields' => 'Custom Fields'
            ]
        ));

        $translation = $translation->fresh();

        $this->assertEquals(1, $block->translations()->count());
        $this->assertEquals($user->id, $translation->author->id);
        $this->assertEquals('New example', $translation->title);
        $this->assertEquals('Body', $translation->body);
        $this->assertEquals('Custom Fields', $translation->custom_fields);
        $this->assertEquals($language->code, $translation->language_code);
        $this->assertTrue($translation->is_active);
    }

    /** @test */
    public function canDeleteInactiveTranslation()
    {
        $withActive = false;
        $block      = $this->tester->haveBlock(
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title',
                        'is_active'     => false
                    ],
                    [
                        'language_code' => 'en',
                        'title'         => 'Updated title title',
                        'is_active'     => true
                    ]
                ]
            ]
        );

        $this->assertEquals(2, $block->translations($withActive)->count());

        dispatch_now(new DeleteBlockTranslation($block->translations($withActive)->first()));

        $this->assertEquals(1, $block->translations($withActive)->count());
    }

    /** @test */
    public function cantDeleteActiveTranslation()
    {
        $block = $this->tester->haveBlock(
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title'
                    ]
                ]
            ]
        );

        try {
            dispatch_now(new DeleteBlockTranslation($block->translations->first()));
        } catch (DomainException $exception) {
            $this->assertEquals('Cannot delete active translation', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function canUpdateBlock()
    {
        $block = $this->tester->haveblock([
            'region'       => null,
            'theme'        => null,
            'weight'       => 0,
            'filter'       => null,
            'options'      => null,
            'is_active'    => false,
            'is_cacheable' => false,
        ]);

        dispatch_now(new UpdateBlock($block, [
            'region'       => 'region',
            'theme'        => 'theme',
            'weight'       => 10,
            'filter'       => 'filter',
            'options'      => 'options',
            'is_active'    => true,
            'is_cacheable' => true,
        ]));

        $block = Block::find($block->id);

        $this->assertTrue($block->is_active);
        $this->assertTrue($block->is_cacheable);
        $this->assertEquals('region', $block->region);
        $this->assertEquals('theme', $block->theme);
        $this->assertEquals('filter', $block->filter);
        $this->assertEquals('options', $block->options);
        $this->assertEquals(10, $block->weight);
    }

    /** @test */
    public function canDeleteBlock()
    {
        $block = $this->tester->haveBlock();

        dispatch_now(new DeleteBlock($block));

        $this->assertNull(Block::find($block->id));
    }
}
