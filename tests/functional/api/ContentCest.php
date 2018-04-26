<?php namespace Cms\api;

use Carbon\Carbon;
use Cms\FunctionalTester;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\UpdateContent;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class ContentCest {

    public function _before(FunctionalTester $I)
    {
        $I->apiLoginAsAdmin();
    }

    public function _after(FunctionalTester $I)
    {
        Carbon::setTestNow();
    }

    public function shouldGetListOfContentsWithDatesConvertedToRequestTimezone(FunctionalTester $I)
    {
        $requestedTimezone = 'Australia/Adelaide';

        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $category = dispatch_now(CreateContent::category('Category Title', $en, $user, [
            'published_at' => Carbon::now()
        ]));

        $content1 = dispatch_now(CreateContent::content('Content 1 Title', $en, $user, [
            'parent_id'    => $category->id,
            'published_at' => Carbon::now()->addDay()
        ]));
        $content2 = dispatch_now(CreateContent::content('Content 2 Title', $en, $user, [
            'parent_id'    => $category->id,
            'published_at' => Carbon::now()->subDay()
        ]));

        $I->haveHttpHeader('Accept-Timezone', $requestedTimezone);
        $I->sendGet(apiUrl('contents'));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson([
            [
                'type'         => 'category',
                'parent_id'    => null,
                'published_at' => $category->published_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
                'created_at'   => $category->created_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
                'updated_at'   => $category->updated_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
            ],
            [
                'type'         => 'content',
                'published_at' => $content2->published_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
                'created_at'   => $content2->created_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
                'updated_at'   => $content2->updated_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
                'translations' => [
                    'title'      => 'Content 2 Title',
                    'created_at' => $content2->translations->first()->created_at->copy()->setTimezone($requestedTimezone)
                        ->toIso8601String(),
                    'updated_at' => $content2->translations->first()->updated_at->copy()->setTimezone($requestedTimezone)
                        ->toIso8601String(),
                ]
            ],
            [
                'type'         => 'content',
                'published_at' => $content1->published_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
                'created_at'   => $content1->created_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
                'updated_at'   => $content1->updated_at->copy()->setTimezone($requestedTimezone)->toIso8601String(),
                'translations' => [
                    'title'      => 'Content 1 Title',
                    'created_at' => $content1->translations->first()->created_at->copy()->setTimezone($requestedTimezone)
                        ->toIso8601String(),
                    'updated_at' => $content1->translations->first()->updated_at->copy()->setTimezone($requestedTimezone)
                        ->toIso8601String(),
                ]
            ],
        ]);
    }

    public function shouldGetListOfContents(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $category = dispatch_now(CreateContent::category('Category Title', $en, $user, [
            'teaser'          => 'Category teaser',
            'body'            => 'Category body',
            'seo_title'       => 'SEO category title',
            'seo_description' => 'SEO category description',
            'is_active'       => true
        ]));

        $content = dispatch_now(CreateContent::content('Content Title', $en, $user, [
            'parent_id' => $category->id,
            'is_active' => true
        ]));

        dispatch_now(new AddContentTranslation($category, 'Tytuł kategorii', $pl, $user, [
            'teaser'          => 'Wstęp kategorii',
            'body'            => 'Treść kategorii',
            'seo_title'       => 'Tytuł SEO kategorii',
            'seo_description' => 'Opis SEO kategorii',
            'is_active'       => true
        ]));

        $I->sendGet(apiUrl('contents'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'type'               => 'category',
                    'parent_id'          => null,
                    'theme'              => null,
                    'weight'             => 0,
                    'rating'             => 0,
                    'is_on_home'         => false,
                    'is_comment_allowed' => false,
                    'is_promoted'        => false,
                    'is_sticky'          => false,
                    'path'               => [$category->id],
                    'routes'             => [
                        [
                            'language_code' => 'en',
                            'path'          => 'category-title',
                            'is_active'     => true,
                        ],
                        [
                            'language_code' => 'pl',
                            'path'          => 'tytul-kategorii',
                            'is_active'     => true,
                        ]
                    ],
                    'translations'       => [
                        [
                            'language_code'   => 'en',
                            'title'           => 'Category Title',
                            'teaser'          => 'Category teaser',
                            'body'            => 'Category body',
                            'seo_title'       => 'SEO category title',
                            'seo_description' => 'SEO category description',
                            'is_active'       => true,
                        ],
                        [
                            'language_code'   => 'pl',
                            'title'           => 'Tytuł kategorii',
                            'teaser'          => 'Wstęp kategorii',
                            'body'            => 'Treść kategorii',
                            'seo_title'       => 'Tytuł SEO kategorii',
                            'seo_description' => 'Opis SEO kategorii',
                            'is_active'       => true,
                        ]
                    ]
                ],
                [
                    'type'         => 'content',
                    'parent_id'    => $category->id,
                    'path'         => [$category->id, $content->id],
                    'routes'       => [
                        [
                            'language_code' => 'en',
                            'path'          => 'category-title/content-title',
                            'is_active'     => true,
                        ]
                    ],
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => 'Content Title',
                            'is_active'     => true,
                        ]
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToGetCategoryChildren(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $category = dispatch_now(CreateContent::category('Category Title', $language, $user, ['is_active' => true]));
        $content  = dispatch_now(CreateContent::content('Content Title', $language, $user, [
            'parent_id' => $category->id,
            'is_active' => true
        ]));

        $I->sendGET(apiUrl("contents/$category->id/children"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'content',
                'parent_id'    => $category->id,
                'path'         => [$category->id, $content->id],
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Content Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'category',
                'parent_id'    => null,
                'path'         => [$category->id],
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Category Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfContentsByCreatedAt(FunctionalTester $I)
    {
        $from      = "2021-05-02T00:43:31+09:30";
        $to        = '2021-05-01T23:43:31-04:00';
        $createdAt = '2021-05-01 18:43:31';

        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content('Content Title', $language, $user, [
            'is_active'  => true,
            'created_at' => $createdAt
        ]));

        $I->sendGET(apiUrl('contents?created_at=' . urlencode($from . ',' . $to)));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl('contents?created_at=' . urlencode('!' . $from . ',' . $to)));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Content Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfContentsByUpdatedAt(FunctionalTester $I)
    {
        $from      = "2021-05-02T00:43:31+09:30";
        $to        = '2021-05-01T23:43:31-04:00';
        $updatedAt = '2021-05-01 18:43:31';
        $createdAt = $updatedAt;

        $content = $I->haveContent([
            'type'         => 'content',
            'created_at'   => $createdAt,
            'updated_at'   => $updatedAt,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago content's content",
                    'is_active'     => true
                ]
            ]
        ]);

        $I->sendGET(apiUrl('contents?updated_at=' . urlencode('!' . $from . ',' . $to)));
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        Carbon::setTestNow(Carbon::parse($updatedAt)->addMinute());
        dispatch_now((new UpdateContent($content, ['is_sticky' => true])));

        $I->sendGET(apiUrl('contents?updated_at=' . urlencode($from . ',' . $to)));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'type'         => 'content',
                    'is_sticky'    => true,
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => "Four day's ago content's content",
                            'is_active'     => true,
                        ]
                    ]
                ]
            ]
        );
    }

    public function shouldFailIfUpdatedAtIsNotDateRangeFormat(FunctionalTester $I)
    {
        $I->sendGET(apiUrl("contents?updated_at=2017-01-01,"));
        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => ['updated_at' => ['The updated at format is invalid.']]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfContentsByPublishedAt(FunctionalTester $I)
    {
        $from = "2021-05-02T00:43:31+09:30";
        $to   = '2021-05-01T23:43:31-04:00';

        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content("content on left edge", $language, $user, [
            'published_at' => Carbon::parse($from),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::content("content behind left edge", $language, $user, [
            'published_at' => Carbon::parse($from)->subSecond(),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::category("category on right edge", $language, $user, [
            'published_at' => Carbon::parse($to),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::category("category behind right edge", $language, $user, [
            'published_at' => Carbon::parse($to)->addSecond(),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::content("content in between", $language, $user, [
            'published_at' => Carbon::parse($from)->addHour(),
            'is_active'    => true
        ]));

        $expectedInsidersJson = [
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => "content in between",
                        'is_active'     => true,
                    ]
                ]
            ],
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => "content on left edge",
                        'is_active'     => true,
                    ]
                ]
            ],
            [
                'type'         => 'category',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => "category on right edge",
                        'is_active'     => true,
                    ]
                ]
            ]
        ];

        $expectedOutsidersJson = [
            [
                'type'         => 'category',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => "category behind right edge",
                        'is_active'     => true,
                    ]
                ]
            ],
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => "content behind left edge",
                        'is_active'     => true,
                    ]
                ]
            ],
        ];

        $I->sendGET(apiUrl('contents?published_at=' . urlencode($from . ',' . $to)));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($expectedInsidersJson);
        $I->dontSeeResponseContainsJson($expectedOutsidersJson);

        $I->sendGET(apiUrl('contents?published_at=' . urlencode('!' . $from . ',' . $to)));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson($expectedOutsidersJson);
        $I->dontSeeResponseContainsJson($expectedInsidersJson);
    }

    public function shouldBeAbleToFilterListOfContentsByType(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::category('Category Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateContent::content('Content Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('contents?type=category'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'category',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Category Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Content Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('contents?type=!category'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Content Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'category',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Category Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToSortListOfContentsByTranslationsTitle(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content('Content Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateContent::category('Category Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('contents?translations[language_code]=en&sort=translations.title'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].translations[0].title');
        $second = $I->grabDataFromResponseByJsonPath('data[1].translations[0].title');

        $I->assertEquals('Category Title', head($first));
        $I->assertEquals('Content Title', head($second));

        $I->sendGET(apiUrl('contents?translations[language_code]=en&sort=-translations.title'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].translations[0].title');
        $second = $I->grabDataFromResponseByJsonPath('data[1].translations[0].title');

        $I->assertEquals('Content Title', head($first));
        $I->assertEquals('Category Title', head($second));
    }

    public function shouldBeAbleToSortListOfContentsByType(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content('Content Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateContent::category('Category Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('contents?sort=type'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].type');
        $second = $I->grabDataFromResponseByJsonPath('data[1].type');

        $I->assertEquals('category', head($first));
        $I->assertEquals('content', head($second));

        $I->sendGET(apiUrl('contents?sort=-type'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].type');
        $second = $I->grabDataFromResponseByJsonPath('data[1].type');

        $I->assertEquals('content', head($first));
        $I->assertEquals('category', head($second));
    }

    public function shouldBeAbleToFilterListOfContentsByLevel(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $category = dispatch_now(CreateContent::category('Category Title', $language, $user, ['is_active' => true]));

        dispatch_now(CreateContent::content('Content Title', $language, $user, [
            'is_active' => true,
            'parent_id' => $category->id
        ]));

        $I->sendGET(apiUrl('contents?level=0'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'category',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Category Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Content Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('contents?level=1'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Content Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'category',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Category Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToSortListOfContentsByLevel(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $category = dispatch_now(CreateContent::category('Category Title', $language, $user, ['is_active' => true]));

        dispatch_now(CreateContent::content('Content Title', $language, $user, [
            'is_active' => true,
            'parent_id' => $category->id
        ]));

        $I->sendGET(apiUrl('contents?sort=level'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].level');
        $second = $I->grabDataFromResponseByJsonPath('data[1].level');

        $I->assertEquals(0, head($first));
        $I->assertEquals(1, head($second));

        $I->sendGET(apiUrl('contents?sort=-level'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].level');
        $second = $I->grabDataFromResponseByJsonPath('data[1].level');

        $I->assertEquals(1, head($first));
        $I->assertEquals(0, head($second));
    }

    public function shouldBeAbleToFilterListOfContentsByAuthorId(FunctionalTester $I)
    {
        $user1    = factory(User::class)->create();
        $user2    = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::category('Category Title', $language, $user1, ['is_active' => true]));
        dispatch_now(CreateContent::content('Content Title', $language, $user2, ['is_active' => true]));

        $I->sendGET(apiUrl('contents?author_id=' . $user1->id));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'category',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Category Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Content Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('contents?author_id=' . $user2->id));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Content Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'category',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Category Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToSortListOfContentsByAuthorId(FunctionalTester $I)
    {
        $user1    = factory(User::class)->create();
        $user2    = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::category('Category Title', $language, $user1, ['is_active' => true]));
        dispatch_now(CreateContent::content('Content Title', $language, $user2, ['is_active' => true]));

        $I->sendGET(apiUrl('contents?sort=author_id'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].author_id');
        $second = $I->grabDataFromResponseByJsonPath('data[1].author_id');

        $I->assertEquals($user1->id, head($first));
        $I->assertEquals($user2->id, head($second));

        $I->sendGET(apiUrl('contents?sort=-author_id'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].author_id');
        $second = $I->grabDataFromResponseByJsonPath('data[1].author_id');

        $I->assertEquals($user2->id, head($first));
        $I->assertEquals($user1->id, head($second));
    }

    public function shouldBeAbleToFilterListOfContentsByIsSticky(FunctionalTester $I)
    {
        $I->haveContents([
            [
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Not Sticked']
                ]
            ],
            [
                'is_sticky'    => true,
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Sticked']
                ]
            ]
        ]);

        $I->sendGET(apiUrl('contents?is_sticky=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Sticked']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not Sticked']
        );

        $I->sendGET(apiUrl('contents?is_sticky=false'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Not Sticked']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Sticked']
        );
    }

    public function shouldBeAbleToFilterListOfContentsByIsPromoted(FunctionalTester $I)
    {
        $I->haveContents([
            [
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Not Promoted']
                ]
            ],
            [
                'is_promoted'  => true,
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Promoted']
                ]
            ]
        ]);

        $I->sendGET(apiUrl('contents?is_promoted=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Promoted']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not Promoted']
        );

        $I->sendGET(apiUrl('contents?is_promoted=false'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Not Promoted']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Promoted']
        );
    }

    public function shouldBeAbleToFilterListOfContentsByIsOnHome(FunctionalTester $I)
    {
        $I->haveContents([
            [
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Not On Homepage']
                ]
            ],
            [
                'is_on_home'   => true,
                'translations' => [
                    ['language_code' => 'en', 'title' => 'On Homepage']
                ]
            ]
        ]);

        $I->sendGET(apiUrl('contents?is_on_home=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'On Homepage']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not On Homepage']
        );

        $I->sendGET(apiUrl('contents?is_on_home=false'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Not On Homepage']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'On Homepage']
        );
    }

    public function shouldBeAbleToFilterListOfContentsByIsCommentAllowed(FunctionalTester $I)
    {
        $I->haveContents([
            [
                'is_comment_allowed' => false,
                'translations'       => [
                    ['language_code' => 'en', 'title' => 'Comments Not Allowed']
                ]
            ],
            [
                'is_comment_allowed' => true,
                'translations'       => [
                    ['language_code' => 'en', 'title' => 'Comments Allowed']
                ]
            ]
        ]);

        $I->sendGET(apiUrl('contents?is_comment_allowed=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Comments Allowed']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Comments Not Allowed']
        );

        $I->sendGET(apiUrl('contents?is_comment_allowed=false'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Comments Not Allowed']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Comments Allowed']
        );
    }

    public function shouldGetSingleContent(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $category = dispatch_now(CreateContent::category('Category Title', $en, $user, [
            'teaser'          => 'Category teaser',
            'body'            => 'Category body',
            'seo_title'       => 'SEO category title',
            'seo_description' => 'SEO category description',
            'is_active'       => true
        ]));

        dispatch_now(new AddContentTranslation($category, 'Tytuł kategorii', $pl, $user, [
            'teaser'          => 'Wstęp kategorii',
            'body'            => 'Treść kategorii',
            'seo_title'       => 'Tytuł SEO kategorii',
            'seo_description' => 'Opis SEO kategorii',
            'is_active'       => true
        ]));

        $I->sendGET(apiUrl('contents', ['id' => $category->id]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'               => 'category',
                'parent_id'          => null,
                'theme'              => null,
                'weight'             => 0,
                'rating'             => 0,
                'is_on_home'         => false,
                'is_comment_allowed' => false,
                'is_promoted'        => false,
                'is_sticky'          => false,
                'path'               => [$category->id],
                'routes'             => [
                    [
                        'language_code' => 'en',
                        'path'          => 'category-title',
                        'is_active'     => true
                    ],
                    [
                        'language_code' => 'pl',
                        'path'          => 'tytul-kategorii',
                        'is_active'     => true
                    ]
                ],
                'translations'       => [
                    [
                        'language_code'   => 'en',
                        'title'           => 'Category Title',
                        'teaser'          => 'Category teaser',
                        'body'            => 'Category body',
                        'seo_title'       => 'SEO category title',
                        'seo_description' => 'SEO category description',
                        'is_active'       => true
                    ],
                    [
                        'language_code'   => 'pl',
                        'title'           => 'Tytuł kategorii',
                        'teaser'          => 'Wstęp kategorii',
                        'body'            => 'Treść kategorii',
                        'seo_title'       => 'Tytuł SEO kategorii',
                        'seo_description' => 'Opis SEO kategorii',
                        'is_active'       => true
                    ]
                ]
            ]
        );
    }

    public function shouldNotBeAbleToGetNonExistingContent(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('contents', ['id' => 100]));
        $I->seeResponseCodeIs(404);
    }

    public function canCreateContent(FunctionalTester $I)
    {
        $I->sendPOST(apiUrl('contents'),
            [
                'type'               => 'content',
                'language_code'      => 'en',
                'title'              => 'Example Title',
                'teaser'             => 'Example Teaser',
                'body'               => 'Example Body',
                'seo_title'          => 'Example SEO Title',
                'seo_description'    => 'Example SEO Description',
                'is_active'          => true,
                'is_on_home'         => true,
                'is_promoted'        => true,
                'is_sticky'          => true,
                'is_comment_allowed' => true,
                'weight'             => 10,
                'theme'              => 'is-content',
            ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'               => 'content',
                'parent_id'          => null,
                'theme'              => 'is-content',
                'weight'             => 10,
                'rating'             => 0,
                'is_on_home'         => true,
                'is_comment_allowed' => true,
                'is_promoted'        => true,
                'is_sticky'          => true,
                'routes'             => [
                    [
                        'language_code' => 'en',
                        'path'          => 'example-title',
                        'is_active'     => true
                    ]
                ],
                'translations'       => [
                    [
                        'language_code'   => 'en',
                        'title'           => 'Example Title',
                        'teaser'          => 'Example Teaser',
                        'body'            => 'Example Body',
                        'seo_title'       => 'Example SEO Title',
                        'seo_description' => 'Example SEO Description',
                        'is_active'       => true
                    ]
                ]
            ]
        );
    }

    public function canUpdateContent(FunctionalTester $I)
    {
        $content = $I->haveContent(
            [
                'parent_id'          => null,
                'type'               => 'content',
                'weight'             => 1,
                'rating'             => 1,
                'is_on_home'         => true,
                'is_comment_allowed' => true,
                'is_promoted'        => true,
                'is_sticky'          => true,
                'theme'              => 'first-content',
                'translations'       => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Some Title',
                        'teaser'        => 'Teaser',
                        'body'          => 'Body',

                    ]
                ]
            ]
        );

        $I->sendPATCH(apiUrl('contents', ['id' => $content->id]),
            [
                'type'               => 'category',
                'is_on_home'         => false,
                'is_promoted'        => false,
                'is_sticky'          => false,
                'is_comment_allowed' => false,
                'published_at'       => Carbon::tomorrow()->toIso8601String(),
                'weight'             => 20,
                'rating'             => 9,
                'theme'              => 'changed-content',
            ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'               => 'content',
                'parent_id'          => null,
                'theme'              => 'changed-content',
                'weight'             => 20,
                'rating'             => 9,
                'is_on_home'         => false,
                'is_comment_allowed' => false,
                'is_promoted'        => false,
                'is_sticky'          => false,
                'routes'             => [
                    ['language_code' => 'en', 'path' => 'some-title', 'is_active' => true]
                ],
                'translations'       => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Some Title',
                        'teaser'        => 'Teaser',
                        'body'          => 'Body',
                        'is_active'     => true
                    ]
                ]
            ]
        );
    }

    public function canCreateContentWithSameTitleMultipleTimes(FunctionalTester $I)
    {
        $I->sendPOST(apiUrl('contents'), ['type' => 'content', 'language_code' => 'en', 'title' => 'Example title']);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'routes' => [
                'language_code' => 'en',
                'path'          => 'example-title'
            ]
        ]);

        $I->sendPOST(apiUrl('contents'), ['type' => 'content', 'language_code' => 'en', 'title' => 'Example title']);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'routes' => [
                'language_code' => 'en',
                'path'          => 'example-title-1'
            ]
        ]);

        $I->sendPOST(apiUrl('contents'), ['type' => 'content', 'language_code' => 'en', 'title' => 'Example title']);

        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson([
            'routes' => [
                'language_code' => 'en',
                'path'          => 'example-title-2'
            ]
        ]);
    }

    public function shouldNotBeAbleToUpdateNonExistingContent(FunctionalTester $I)
    {
        $I->sendPATCH(apiUrl('contents', ['id' => 100]));
        $I->seeResponseCodeIs(404);
    }

    public function canDeleteContent(FunctionalTester $I)
    {
        $content = $I->haveContent(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Some Title',
                        'teaser'        => 'Teaser',
                        'body'          => 'Body',

                    ]
                ]
            ]
        );

        $I->sendDELETE(apiUrl('contents', ['id' => $content->id]));

        $I->seeResponseCodeIs(204);
    }

    public function shouldNotBeAbleToDeleteNonExistingContent(FunctionalTester $I)
    {
        $I->sendDELETE(apiUrl('contents', ['id' => 100]));
        $I->seeResponseCodeIs(404);
    }

    public function shouldBeAbleToGetContentTranslations(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $content = dispatch_now(CreateContent::content('Original Title', $en, $user, [
            'teaser'          => 'Original translation',
            'body'            => 'Original body',
            'seo_title'       => 'Original SEO title',
            'seo_description' => 'Original SEO description',
            'is_active'       => true
        ]));

        dispatch_now(new AddContentTranslation($content, 'Modified title', $en, $user, [
            'teaser'          => 'Modified teaser',
            'body'            => 'Modified body',
            'seo_title'       => 'Modified SEO title',
            'seo_description' => 'Modified SEO description',
            'is_active'       => true
        ]));

        dispatch_now(new AddContentTranslation($content, 'Oryginalny Tytuł', $pl, $user, [
            'teaser'          => 'Oryginalny wstęp',
            'body'            => 'Oryginalna treść',
            'seo_title'       => 'Oryginalny tytuł SEO',
            'seo_description' => 'Oryginalny opis SEO',
            'is_active'       => true
        ]));

        dispatch_now(new AddContentTranslation($content, 'Nowy Tytuł', $pl, $user, [
            'teaser'          => 'Nowy wstęp',
            'body'            => 'Nowa treść',
            'seo_title'       => 'Nowy tytuł SEO',
            'seo_description' => 'Nowy opis SEO',
            'is_active'       => true
        ]));

        $I->sendGET(apiUrl("contents/$content->id/translations"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'language_code'   => 'en',
                    'title'           => 'Original Title',
                    'teaser'          => 'Original translation',
                    'body'            => 'Original body',
                    'seo_title'       => 'Original SEO title',
                    'seo_description' => 'Original SEO description',
                    'is_active'       => false
                ],
                [
                    'language_code'   => 'en',
                    'title'           => 'Modified title',
                    'teaser'          => 'Modified teaser',
                    'body'            => 'Modified body',
                    'seo_title'       => 'Modified SEO title',
                    'seo_description' => 'Modified SEO description',
                    'is_active'       => true
                ],
                [
                    'language_code'   => 'pl',
                    'title'           => 'Oryginalny Tytuł',
                    'teaser'          => 'Oryginalny wstęp',
                    'body'            => 'Oryginalna treść',
                    'seo_title'       => 'Oryginalny tytuł SEO',
                    'seo_description' => 'Oryginalny opis SEO',
                    'is_active'       => false
                ],
                [
                    'language_code'   => 'pl',
                    'title'           => 'Nowy Tytuł',
                    'teaser'          => 'Nowy wstęp',
                    'body'            => 'Nowa treść',
                    'seo_title'       => 'Nowy tytuł SEO',
                    'seo_description' => 'Nowy opis SEO',
                    'is_active'       => true
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfContentTranslationsByLanguageCode(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $content = dispatch_now(CreateContent::content('Example Title', $en, $user));

        dispatch_now(new AddContentTranslation($content, 'Przykładowy Tytuł', $pl, $user));

        $I->sendGET(apiUrl("contents/$content->id/translations?language_code=en"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['language_code' => 'en', 'title' => 'Example Title']
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['language_code' => 'pl', 'title' => 'Przykładowy Tytuł']
            ]
        );

        $I->sendGET(apiUrl("contents/$content->id/translations?language_code=pl"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['language_code' => 'pl', 'title' => 'Przykładowy Tytuł']
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['language_code' => 'en', 'title' => 'Example Title']
            ]
        );
    }

    public function shouldBeAbleToFilterListOfContentTranslationsByIsActive(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $content = dispatch_now(CreateContent::content('Original Title', $language, $user));

        dispatch_now(new AddContentTranslation($content, 'Modified title', $language, $user));

        $I->sendGET(apiUrl("contents/$content->id/translations?is_active=true"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['language_code' => 'en', 'title' => 'Modified title', 'is_active' => true]
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['language_code' => 'en', 'title' => 'Original Title', 'is_active' => false]
            ]
        );

        $I->sendGET(apiUrl("contents/$content->id/translations?is_active=false"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['language_code' => 'en', 'title' => 'Original Title', 'is_active' => false]
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['language_code' => 'en', 'title' => 'Modified Title', 'is_active' => true]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfContentTranslationsByAuthorId(FunctionalTester $I)
    {
        $user1    = factory(User::class)->create();
        $user2    = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $content = dispatch_now(CreateContent::content('Example Title', $language, $user1));

        dispatch_now(new AddContentTranslation($content, 'Translation from first user', $language, $user1));
        dispatch_now(new AddContentTranslation($content, 'Translation from second user', $language, $user2));

        $I->sendGET(apiUrl("contents/$content->id/translations?author_id=$user1->id"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => 'Translation from first user']
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['title' => 'Translation from second user']
            ]
        );

        $I->sendGET(apiUrl("contents/$content->id/translations?author_id=$user2->id"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => 'Translation from second user']
            ]
        );

        $I->dontSeeResponseContainsJson(
            [
                ['title' => 'Translation from first user']
            ]
        );
    }

    public function shouldBeAbleToFilterListOfContentTranslationsByCreatedAt(FunctionalTester $I)
    {
        $from      = "2021-05-02T00:43:31+09:30";
        $to        = '2021-05-01T23:43:31-04:00';
        $createdAt = '2021-05-01 18:43:31';

        $content = $I->haveContent([
            'type'         => 'content',
            'created_at'   => $createdAt,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago content's content",
                    'is_active'     => true,
                    'created_at'    => $createdAt
                ]
            ]
        ]);

        $I->sendGET(apiUrl('contents/' . $content->id . '/translations?created_at=' . urlencode('!' . $from . ',' . $to)));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl('contents/' . $content->id . '/translations?created_at=' . urlencode($from . ',' . $to)));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => "Four day's ago content's content"]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfContentTranslationsByUpdatedAt(FunctionalTester $I)
    {
        $from      = "2021-05-02T00:43:31+09:30";
        $to        = '2021-05-01T23:43:31-04:00';
        $createdAt = '2021-05-01 18:43:31';
        $updatedAt = '2021-05-01 19:41:12';

        $content = $I->haveContent([
            'type'         => 'content',
            'created_at'   => $createdAt,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago content's content",
                    'is_active'     => true,
                    'updated_at'    => $updatedAt
                ]
            ]
        ]);

        $I->sendGET(apiUrl('contents/' . $content->id . '/translations?updated_at=' . urlencode('!' . $from . ',' . $to)));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl('contents/' . $content->id . '/translations?updated_at=' . urlencode($from . ',' . $to)));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => "Four day's ago content's content"]
            ]
        );
    }

    public function canCreateContentTranslation(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $content = dispatch_now(CreateContent::content('Original Title', $language, $user));

        $I->sendPOST(apiUrl("contents/$content->id/translations"),
            [
                'language_code'   => 'en',
                'title'           => 'Example Title',
                'teaser'          => 'Example Teaser',
                'body'            => 'Example Body',
                'seo_title'       => 'Example SEO Title',
                'seo_description' => 'Example SEO Description'
            ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'language_code'   => 'en',
                'title'           => 'Example Title',
                'teaser'          => 'Example Teaser',
                'body'            => 'Example Body',
                'seo_title'       => 'Example SEO Title',
                'seo_description' => 'Example SEO Description',
                'is_active'       => true
            ]
        );

        $I->sendPOST(apiUrl("contents/$content->id/translations"),
            [
                'language_code'   => 'pl',
                'title'           => 'Nowy Tytuł',
                'teaser'          => 'Nowy wstęp',
                'body'            => 'Nowa treść',
                'seo_title'       => 'Nowy tytuł SEO',
                'seo_description' => 'Nowy opis SEO'
            ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'language_code'   => 'pl',
                'title'           => 'Nowy Tytuł',
                'teaser'          => 'Nowy wstęp',
                'body'            => 'Nowa treść',
                'seo_title'       => 'Nowy tytuł SEO',
                'seo_description' => 'Nowy opis SEO',
                'is_active'       => true
            ]
        );
    }

    public function canDeleteInactiveContentTranslation(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $content = dispatch_now(CreateContent::content('Example Title', $language, $user));

        $translation = dispatch_now(new AddContentTranslation($content, 'Inactive Translation', $language, $user));

        dispatch_now(new AddContentTranslation($content, 'New Active Title', $language, $user));

        $I->sendDELETE(apiUrl("contents/$content->id/translations", ['translationId' => $translation->id]));

        $I->seeResponseCodeIs(204);
    }

    public function cantDeleteActiveContentTranslation(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $content = dispatch_now(CreateContent::content('Example Title', $en, $user));

        $translation = dispatch_now(new AddContentTranslation($content, 'Przykładowy Tytuł', $pl, $user));

        $I->sendDELETE(apiUrl("contents/$content->id/translations", ['translationId' => $translation->id]));

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(['message' => 'Cannot delete active translation']);
    }

    public function shouldBeAbleToSeeContentsAsTree(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $category = dispatch_now(CreateContent::category('Original Title', $language, $user));
        $content  = dispatch_now(CreateContent::content('Original Title', $language, $user, ['parent_id' => $category->id]));

        $I->sendGET(apiUrl("contents-tree"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'data' => [
                    [
                        'id'       => $category->id,
                        'children' => [['id' => $content->id]]
                    ]
                ],
            ]
        );
    }

    public function shouldBeAbleToSeeOnlyCategoriesAsTree(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $category  = dispatch_now(CreateContent::category('Original Title', $language, $user));
        $category2 = dispatch_now(CreateContent::category('Original Title', $language, $user, ['parent_id' => $category->id]));
        $content   = dispatch_now(CreateContent::content('Original Title', $language, $user, ['parent_id' => $category->id]));

        $I->sendGET(apiUrl("contents-tree?only_categories=true"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->dontSeeResponseContainsJson([
            'data' => [
                [
                    'id'       => $category->id,
                    'children' => [['id' => $content->id]]
                ]
            ],
        ]);
        $I->seeResponseContainsJson(
            [
                'data' => [
                    [
                        'id'       => $category->id,
                        'children' => [['id' => $category2->id]]
                    ]
                ],
            ]
        );
    }

    public function shouldBeAbleToSeeMoreThanOneTree(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $category  = dispatch_now(CreateContent::category('Original Title', $language, $user));
        $category2 = dispatch_now(CreateContent::category('Original1 Title', $language, $user, ['parent_id' => $category->id]));
        $category3 = dispatch_now(CreateContent::category('Original2 Title', $language, $user));
        $content   = dispatch_now(CreateContent::content('Original3 Title', $language, $user, ['parent_id' => $category3->id]));

        $I->sendGET(apiUrl("contents-tree"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson([
            'data' => [
                [
                    'id'       => $category->id,
                    'children' => [['id' => $category2->id]]
                ],
                [
                    'id'       => $category3->id,
                    'children' => [['id' => $content->id]]
                ]
            ],
        ]);


        $I->sendGET(apiUrl("contents-tree?only_categories=true"));

        $I->seeResponseContainsJson([
            'data' => [
                [
                    'id'       => $category->id,
                    'children' => [['id' => $category2->id]]
                ],
                [
                    'id'       => $category3->id,
                    'children' => []
                ]
            ],
        ]);
    }

    public function shouldReturnEmptyResultOnEmptyDB(FunctionalTester $I)
    {
        $I->sendGET(apiUrl("contents-tree"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['data' => []]);
    }

    public function shouldBeAbleToFilterListOfContentsToGetOnlyPublishedOnes(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        dispatch_now(CreateContent::content('Content Title', $en, $user, [
            'is_active'    => true,
            'published_at' => Carbon::now()
        ]));
        dispatch_now(CreateContent::category('Tytuł kategorii', $pl, $user, [
            'is_active'    => false,
            'published_at' => Carbon::now()
        ]));

        $I->sendGET(apiUrl('contents?only_published=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson([
            'data' => [
                ['title' => 'Content Title']
            ]
        ]);
        $I->dontSeeResponseContainsJson([
            'data' => [
                ['title' => 'Tytuł kategorii']
            ]
        ]);
    }

    public function shouldBeAbleToFilterListOfContentsToGetOnlyNotPublishedOnes(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content('Active Title', $language, $user, [
            'is_active'    => false,
            'published_at' => Carbon::now()
        ]));
        dispatch_now(CreateContent::category('Inactive Title', $language, $user, [
            'is_active'    => true,
            'published_at' => Carbon::now()
        ]));

        $I->sendGET(apiUrl('contents?only_not_published=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson([
            'data' => [
                ['title' => 'Active Title']
            ]
        ]);
        $I->dontSeeResponseContainsJson([
            'data' => [
                ['title' => 'Inactive Title']
            ]
        ]);
    }

    public function shouldBeAbleToFilterListOfContentsByRatingRange(FunctionalTester $I)
    {
        $I->haveContents([
            [
                'rating'       => 1,
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Rating 1']
                ]
            ],
            [
                'rating'       => 2,
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Rating 2']
                ]
            ],
            [
                'rating'       => 3,
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Rating 3']
                ]
            ],
            [
                'rating'       => 4,
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Rating 4']
                ]
            ],
            [
                'rating'       => 5,
                'translations' => [
                    ['language_code' => 'en', 'title' => 'Rating 5']
                ]
            ]
        ]);

        $I->sendGET(apiUrl('contents?rating=2,4'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Rating 2'],
            ['title' => 'Rating 3'],
            ['title' => 'Rating 4']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Rating 1'],
            ['title' => 'Rating 5']
        );

        $I->sendGET(apiUrl('contents?rating=!2,4'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Rating 1'],
            ['title' => 'Rating 5']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Rating 2'],
            ['title' => 'Rating 3'],
            ['title' => 'Rating 4']
        );
    }

    public function canUpdateContentRoute(FunctionalTester $I)
    {
        $user     = $I->haveUser();
        $language = new Language(['code' => 'en']);
        $content  = dispatch_now(CreateContent::content('New One', $language, $user, [
            'is_active' => true
        ]));

        $I->sendPATCH(apiUrl("contents/$content->id/route"),
            [
                'language_code' => 'en',
                'path'          => 'new-path',
                'is_active'     => false
            ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'routes' => [
                    ['language_code' => 'en', 'path' => 'new-path', 'is_active' => false]
                ]
            ]
        );
    }
}
