<?php namespace Cms;

use Carbon\Carbon;
use Codeception\Test\Unit;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Models\Language;
use Gzero\Core\Query\QueryBuilder;
use Gzero\InvalidArgumentException;

class ContentReadRepositoryTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var ContentReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new ContentReadRepository();
    }

    /** @test */
    public function canGetContentByPath()
    {
        $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                    'is_active'     => false
                ]
            ]
        ]);

        // inactive route translations as default
        $content          = $this->repository->getByPath('example-title', 'en');
        $routeTranslation = $content->routes->first();

        $this->assertEquals('example-title', $routeTranslation->path);
        $this->assertEquals('en', $routeTranslation->language_code);
        // Only active route translations
        $this->assertNull($this->repository->getByPath('example-title', 'en', true));
    }

    /** @test */
    public function getAncestorsTitlesAndPaths()
    {
        $language    = Language::find(['code' => 'en'])->first();
        $grandParent = $this->tester->haveContent([
            'type'         => 'category',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Grand parent title'
                ]
            ]
        ]);
        $parent      = $this->tester->haveContent([
            'type'         => 'category',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Parent title'
                ]
            ]
        ]);
        $child       = $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Child title'
                ]
            ]
        ]);

        $parent->setChildOf($grandParent);
        $child->setChildOf($parent);

        $titles = $this->repository->getAncestorsTitlesAndPaths($child, $language)->toArray();

        $this->assertEquals('Grand parent title', $titles[0]->title);
        $this->assertEquals('grand-parent-title', $titles[0]->path);
        $this->assertEquals("$grandParent->id/", $grandParent->path);

        $this->assertEquals('Parent title', $titles[1]->title);
        $this->assertEquals('parent-title', $titles[1]->path);
        $this->assertEquals("$grandParent->id/$parent->id/", $parent->path);

        $this->assertEquals('Child title', $titles[2]->title);
        $this->assertEquals('child-title', $titles[2]->path);
        $this->assertEquals("$grandParent->id/$parent->id/$child->id/", $child->path);
    }

    /** @test */
    public function canAddConditionsToGetMany()
    {
        $this->tester->haveContents([
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Other title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Inactive title',
                        'is_active'     => false
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'pl',
                        'title'         => 'Example polish title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'pl',
                        'title'         => 'Other polish title'
                    ]
                ]
            ]
        ]);

        $result = $this->repository->getMany(
            (new QueryBuilder)
                ->where('translations.is_active', '=', true)
                ->where('translations.language_code', '=', 'en')
                ->orderBy('id', 'asc')
        );

        $this->assertEquals(2, $result->count());
        $this->assertEquals('en', $result->first()->translations->first()->language_code);
        $this->assertEquals('en', $result->last()->translations->first()->language_code);
    }

    /** @test */
    public function shouldCheckDependantField()
    {
        try {
            $this->repository->getMany(
                (new QueryBuilder)
                    ->where('translations.is_active', '=', true)
                    ->orderBy('id', 'asc')
            );
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Language code is required', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function canPaginateResults()
    {
        $this->tester->haveContents([
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'A title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'B title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'C title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'D title'
                    ]
                ]
            ]
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
    public function shouldHideTranslationsForInactiveRoutes()
    {
        $this->tester->haveContents([
            [
                'published_at' => Carbon::now()->subDays(4),
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Inactive translation title',
                        'is_active'     => false // under the hood, we're creating inactive route too
                    ],
                    [
                        'language_code' => 'en',
                        'title'         => 'Active translation title',
                        'is_active'     => true
                    ],
                    [
                        'language_code' => 'pl',
                        'title'         => 'Example active english translation four days ago',
                        'is_active'     => true
                    ],
                ]
            ]
        ]);

        $result = $this->repository->getManyPublished(new QueryBuilder());

        $this->assertEquals(1, $result->count());
        $this->assertEquals(1, $result->total());

        $content = $result->first();

        $this->assertEmpty($content->translations->firstWhere('language_code', 'en'));
        $this->assertNotEmpty($content->translations->firstWhere('language_code', 'pl'));
        $this->assertCount(1, $content->routes);
        $this->assertNotEmpty($content->routes->firstWhere('language_code', 'pl'));
    }

    /** @test */
    public function shouldNotReturnContentWhichPublishedAtSetToNull()
    {
        $this->tester->haveContents([
            [
                'published_at' => null,
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Active translation title with published date set to null'
                    ]
                ]
            ]
        ]);

        $result = $this->repository->getManyPublished(new QueryBuilder());

        $this->assertEquals(0, $result->count());
        $this->assertEquals(0, $result->total());
    }

    /** @test */
    public function shouldReturnNonDuplicateContent()
    {
        $this->tester->haveContents([
            [
                'published_at' => Carbon::now()->subDays(1),
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Active english translation title 1',
                        'is_active'     => true
                    ],
                    [
                        'language_code' => 'pl',
                        'title'         => 'Active polish translation title 2',
                        'is_active'     => true
                    ],
                    [
                        'language_code' => 'en',
                        'title'         => 'Active translation title 3',
                        'is_active'     => false
                    ]
                ]
            ]
        ]);

        $result = $this->repository->getManyPublished(new QueryBuilder());

        // check how much content is returned
        $this->assertEquals(1, $result->count());
        $this->assertEquals(1, $result->total());
        // check how much entries for routes and translations there is in content
        $this->assertCount(2, $result->items()[0]->translations);
        $this->assertCount(2, $result->items()[0]->routes);
        // check if we expect this translations in this content
        $this->assertEquals(
            'Active english translation title 1',
            $result->items()[0]->translations->firstWhere('language_code', 'en')->title
        );
        $this->assertEquals(
            'Active polish translation title 2',
            $result->items()[0]->translations->firstWhere('language_code', 'pl')->title
        );
        // check if we expect this routes in this content
        $this->assertEquals(
            'active-english-translation-title-1',
            $result->items()[0]->routes->firstWhere('language_code', 'en')->path
        );
        $this->assertEquals(
            'active-polish-translation-title-2',
            $result->items()[0]->routes->firstWhere('language_code', 'pl')->path
        );
    }
}

