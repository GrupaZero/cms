<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Repositories\BlockReadRepository;
use Gzero\Core\Models\Language;
use Gzero\Core\Query\QueryBuilder;

class BlockReadRepositoryTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var BlockReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new BlockReadRepository();
    }

    /** @test */
    public function canPaginateResults()
    {
        $this->tester->haveBlocks([
            ['translations' => [['language_code' => 'en', 'title' => 'A title']]],
            ['translations' => [['language_code' => 'en', 'title' => 'B title']]],
            ['translations' => [['language_code' => 'en', 'title' => 'C title']]],
            ['translations' => [['language_code' => 'en', 'title' => 'D title']]]
        ]);

        $result = $this->repository->getMany(
            (new QueryBuilder)
                ->where('translations.is_active', '=', true)
                ->where('translations.language_code', '=', 'en')
                ->orderBy('translations.title', 'desc')
                ->setPageSize(2)
                ->setPage(2)
        );

        $this->assertEquals(2, $result->count());
        $this->assertEquals(2, $result->perPage());
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals('B title', $result->first()->translations->first()->title);
        $this->assertEquals('A title', $result->last()->translations->first()->title);
    }

    /** @test */
    public function getVisibleBlocksWithActiveTranslationInSpecifiedLanguage()
    {
        $language = new Language(['code' => 'en']);

        $block1 = $this->tester->haveBlock([
            'weight'       => 0,
            'is_active'    => true,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'First block title',
                    'is_active'     => true
                ]
            ]
        ]);

        $block2 = $this->tester->haveBlock([
            'weight'       => 1,
            'is_active'    => true,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Second block title',
                    'is_active'     => false
                ]
            ]
        ]);

        $blocks      = $this->repository->getVisibleBlocks([$block1->id, $block2->id], $language);
        $block       = head($blocks->toArray());
        $translation = head($block['translations']);

        $this->assertEquals(1, count($blocks));
        $this->assertEquals('First block title', $translation['title']);
        $this->assertEquals('en', $translation['language_code']);
        $this->assertTrue($block['is_active']);
        $this->assertTrue($translation['is_active']);
    }

    /** @test */
    public function getVisibleBlocksWithoutActiveTranslationInSpecifiedLanguage()
    {
        $language = new Language(['code' => 'en']);

        $block1 = $this->tester->haveBlock([
            'weight'       => 0,
            'is_active'    => true,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'First block title',
                    'is_active'     => true
                ]
            ]
        ]);

        $block2 = $this->tester->haveBlock([
            'weight'       => 1,
            'filter'       => ['+' => ['1/*']],
            'is_active'    => false,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Second block title',
                    'is_active'     => false
                ]
            ]
        ]);

        $blocks       = $this->repository->getVisibleBlocks([$block1->id, $block2->id], $language, false)->toArray();
        $block1       = head($blocks);
        $block2       = last($blocks);
        $translation1 = head($block1['translations']);
        $translation2 = head($block2['translations']);

        $this->assertEquals(2, count($blocks));
        $this->assertTrue($block1['is_active']);
        $this->assertEquals('First block title', $translation1['title']);
        $this->assertEquals('en', $translation1['language_code']);
        $this->assertTrue($translation1['is_active']);

        $this->assertFalse($block2['is_active']);
        $this->assertNull($translation2['title']);
        $this->assertNull($translation2['language_code']);
        $this->assertNull($translation2['is_active']);
    }
}

