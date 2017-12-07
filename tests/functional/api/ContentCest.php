<?php namespace Cms\api;

use Carbon\Carbon;
use Cms\FunctionalTester;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\UpdateContent;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class ContentCest {

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
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $tomorrow   = Carbon::tomorrow()->format('Y-m-d');

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
        $I->dontSeeResponseContainsJson(
            [
                [
                    'type'         => 'content',
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

        dispatch_now((new UpdateContent($content, [
            'is_sticky' => true
        ])));

        $I->sendGET(apiUrl("contents?updated_at=$yesterday,$tomorrow"));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
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
}
