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
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content('Content Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('contents?created_at=2017-10-09,2017-10-10'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl('contents?created_at=!2017-10-09,2017-10-10'));

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
        $fourDaysAgo = Carbon::now()->subDays(4);
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $tomorrow    = Carbon::tomorrow()->format('Y-m-d');

        $content = $I->haveContent([
            'type'         => 'content',
            'created_at'   => $fourDaysAgo,
            'updated_at'   => $fourDaysAgo,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago content's content",
                    'is_active'     => true
                ]
            ]
        ]);

        $I->sendGET(apiUrl("contents?updated_at=$yesterday,$tomorrow"));
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        dispatch_now((new UpdateContent($content, ['is_sticky' => true])));

        $I->sendGET(apiUrl("contents?updated_at=$yesterday,$tomorrow"));
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
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content("Tomorrow's content", $language, $user, [
            'published_at' => Carbon::tomorrow(),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::content("Today's content", $language, $user, [
            'published_at' => Carbon::today(),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::category("Three day's ago content", $language, $user, [
            'published_at' => Carbon::now()->subDays(3),
            'is_active'    => true
        ]));

        $start = Carbon::yesterday()->format('Y-m-d');
        $end   = Carbon::tomorrow()->format('Y-m-d');
        $I->sendGET(apiUrl("contents?published_at=$start,$end"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                [
                    'type'         => 'content',
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => "Today's content",
                            'is_active'     => true,
                        ]
                    ]
                ],
                [
                    'type'         => 'content',
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => "Tomorrow's content",
                            'is_active'     => true,
                        ]
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
                        'title'         => "Three day's ago content",
                        'is_active'     => true,
                    ]
                ]
            ]
        );
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

    public function shouldBeAbleToFilterListOfContentsByIsSticked(FunctionalTester $I)
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

        $I->sendGET(apiUrl('contents?is_sticky=1'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Sticked']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not Sticked']
        );

        $I->sendGET(apiUrl('contents?is_sticky=0'));

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

        $I->sendGET(apiUrl('contents?is_promoted=1'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Promoted']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not Promoted']
        );

        $I->sendGET(apiUrl('contents?is_promoted=0'));

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

        $I->sendGET(apiUrl('contents?is_on_home=1'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'On Homepage']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not On Homepage']
        );

        $I->sendGET(apiUrl('contents?is_on_home=0'));

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

        $I->sendGET(apiUrl('contents?is_comment_allowed=1'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Comments Allowed']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Comments Not Allowed']
        );

        $I->sendGET(apiUrl('contents?is_comment_allowed=0'));

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
                'published_at'       => Carbon::now()->toIso8601String(),
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
}
