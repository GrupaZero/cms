<?php namespace Cms\api;

use Carbon\Carbon;
use Cms\FunctionalTester;
use Gzero\Cms\Jobs\AddBlockTranslation;
use Gzero\Cms\Jobs\CreateBlock;
use Gzero\Cms\Jobs\UpdateBlock;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class BlockCest {

    public function _before(FunctionalTester $I)
    {
        $I->apiLoginAsAdmin();
    }

    public function shouldGetListOfBlocks(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $block = dispatch_now(CreateBlock::basic('New Block', $en, $user, [
            'region'        => 'region',
            'theme'         => 'theme',
            'weight'        => 10,
            'filter'        => 'filter',
            'options'       => 'options',
            'body'          => 'Body',
            'custom_fields' => 'Custom fields',
            'is_active'     => true,
            'is_cacheable'  => true
        ]));

        dispatch_now(new AddBlockTranslation($block, 'Nowy Blok', $pl, $user,
            [
                'body'          => 'Treść',
                'custom_fields' => 'Pola'
            ]
        ));

        $I->sendGet(apiUrl('blocks'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'type'         => 'basic',
                    'region'       => 'region',
                    'theme'        => 'theme',
                    'weight'       => 10,
                    'filter'       => 'filter',
                    'options'      => 'options',
                    'is_active'    => true,
                    'is_cacheable' => true,
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => 'New Block',
                            'body'          => 'Body',
                            'custom_fields' => 'Custom fields',
                            'is_active'     => true,
                        ],
                        [
                            'language_code' => 'pl',
                            'title'         => 'Nowy Blok',
                            'body'          => 'Treść',
                            'custom_fields' => 'Pola',
                            'is_active'     => true,
                        ]
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfBlocksByCreatedAt(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('Block Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('blocks?created_at=2017-10-09,2017-10-10'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl('blocks?created_at=!2017-10-09,2017-10-10'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Block Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfBlocksByUpdatedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4);
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $tomorrow    = Carbon::tomorrow()->format('Y-m-d');

        $block = $I->haveBlock([
            'type'         => 'basic',
            'created_at'   => $fourDaysAgo,
            'updated_at'   => $fourDaysAgo,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago block's content",
                    'is_active'     => true
                ]
            ]
        ]);

        $I->sendGET(apiUrl("blocks?updated_at=$yesterday,$tomorrow"));
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        dispatch_now((new UpdateBlock($block, ['theme' => 'new-theme'])));

        $I->sendGET(apiUrl("blocks?updated_at=$yesterday,$tomorrow"));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'type'         => 'basic',
                    'theme'        => 'new-theme',
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => "Four day's ago block's content",
                            'is_active'     => true,
                        ]
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfBlocksByType(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('Basic Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateBlock::slider('Slider Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('blocks?type=basic'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Basic Title',
                        'is_active'     => true
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'slider',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Slider Title',
                        'is_active'     => true
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('blocks?type=!basic'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'slider',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Slider Title',
                        'is_active'     => true
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Basic Title',
                        'is_active'     => true
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToSortListOfBlocksByType(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('Basic Title', $language, $user, ['is_active' => true]));
        dispatch_now(CreateBlock::slider('Slider Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('blocks?sort=type'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].type');
        $second = $I->grabDataFromResponseByJsonPath('data[1].type');

        $I->assertEquals('basic', head($first));
        $I->assertEquals('slider', head($second));

        $I->sendGET(apiUrl('blocks?sort=-type'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].type');
        $second = $I->grabDataFromResponseByJsonPath('data[1].type');

        $I->assertEquals('slider', head($first));
        $I->assertEquals('basic', head($second));
    }

    public function shouldBeAbleToFilterListOfBlocksByAuthorId(FunctionalTester $I)
    {
        $user1    = factory(User::class)->create();
        $user2    = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('First Title', $language, $user1, ['is_active' => true]));
        dispatch_now(CreateBlock::basic('Second Title', $language, $user2, ['is_active' => true]));

        $I->sendGET(apiUrl('blocks?author_id=' . $user1->id));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'First Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Second Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('blocks?author_id=' . $user2->id));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Second Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'First Title',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToSortListOfBlocksByAuthorId(FunctionalTester $I)
    {
        $user1    = factory(User::class)->create();
        $user2    = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('First Title', $language, $user1, ['is_active' => true]));
        dispatch_now(CreateBlock::basic('Second Title', $language, $user2, ['is_active' => true]));

        $I->sendGET(apiUrl('blocks?sort=author_id'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].author_id');
        $second = $I->grabDataFromResponseByJsonPath('data[1].author_id');

        $I->assertEquals($user1->id, head($first));
        $I->assertEquals($user2->id, head($second));

        $I->sendGET(apiUrl('blocks?sort=-author_id'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].author_id');
        $second = $I->grabDataFromResponseByJsonPath('data[1].author_id');

        $I->assertEquals($user2->id, head($first));
        $I->assertEquals($user1->id, head($second));
    }

    public function shouldBeAbleToFilterListOfBlocksByIsActive(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('Active', $language, $user, ['is_active' => true]));
        dispatch_now(CreateBlock::basic('Not Active', $language, $user, ['is_active' => false]));

        $I->sendGET(apiUrl('blocks?is_active=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Active']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not Active']
        );

        $I->sendGET(apiUrl('blocks?is_active=false'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Not Active']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Active']
        );
    }

    public function shouldBeAbleToFilterListOfBlocksByIsCacheable(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('Cacheable', $language, $user, ['is_cacheable' => true]));
        dispatch_now(CreateBlock::basic('Not Cacheable', $language, $user, ['is_cacheable' => false]));

        $I->sendGET(apiUrl('blocks?is_cacheable=true'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Cacheable']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Not Cacheable']
        );

        $I->sendGET(apiUrl('blocks?is_cacheable=false'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            ['title' => 'Not Cacheable']
        );
        $I->dontSeeResponseContainsJson(
            ['title' => 'Cacheable']
        );
    }

    public function shouldBeAbleToFilterListOfBlocksByRegion(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('Homepage', $language, $user, ['is_active' => true, 'region' => 'homepage']));
        dispatch_now(CreateBlock::basic('Sidebar', $language, $user, ['is_active' => true, 'region' => 'sidebarLeft']));

        $I->sendGET(apiUrl('blocks?region=homepage'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Homepage',
                        'is_active'     => true
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Sidebar',
                        'is_active'     => true
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('blocks?region=!homepage'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Sidebar',
                        'is_active'     => true
                    ]
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                'type'         => 'basic',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Homepage',
                        'is_active'     => true
                    ]
                ]
            ]
        );
    }

    public function shouldBeAbleToSortListOfBlocksByRegion(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('Homepage', $language, $user, ['is_active' => true, 'region' => 'homepage']));
        dispatch_now(CreateBlock::basic('Sidebar', $language, $user, ['is_active' => true, 'region' => 'sidebarLeft']));

        $I->sendGET(apiUrl('blocks?sort=region'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].region');
        $second = $I->grabDataFromResponseByJsonPath('data[1].region');

        $I->assertEquals('homepage', head($first));
        $I->assertEquals('sidebarLeft', head($second));

        $I->sendGET(apiUrl('blocks?sort=-region'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');

        $first  = $I->grabDataFromResponseByJsonPath('data[0].region');
        $second = $I->grabDataFromResponseByJsonPath('data[1].region');

        $I->assertEquals('sidebarLeft', head($first));
        $I->assertEquals('homepage', head($second));
    }

    public function shouldGetSingleBlock(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $block = dispatch_now(CreateBlock::basic('New Block', $en, $user, [
            'region'        => 'region',
            'theme'         => 'theme',
            'weight'        => 10,
            'filter'        => 'filter',
            'options'       => 'options',
            'body'          => 'Body',
            'custom_fields' => 'Custom fields',
            'is_active'     => true,
            'is_cacheable'  => true
        ]));

        dispatch_now(new AddBlockTranslation($block, 'Nowy Blok', $pl, $user,
            [
                'body'          => 'Treść',
                'custom_fields' => 'Pola'
            ]
        ));

        $I->sendGET(apiUrl('blocks', ['id' => $block->id]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'region'       => 'region',
                'theme'        => 'theme',
                'weight'       => 10,
                'filter'       => 'filter',
                'options'      => 'options',
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'New Block',
                        'body'          => 'Body',
                        'custom_fields' => 'Custom fields',
                        'is_active'     => true,
                    ],
                    [
                        'language_code' => 'pl',
                        'title'         => 'Nowy Blok',
                        'body'          => 'Treść',
                        'custom_fields' => 'Pola',
                        'is_active'     => true,
                    ]
                ]
            ]
        );
    }

    public function shouldNotBeAbleToGetNonExistingBlock(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('blocks', ['id' => 100]));
        $I->seeResponseCodeIs(404);
    }

    public function canCreateBlock(FunctionalTester $I)
    {
        $I->sendPOST(apiUrl('blocks'),
            [
                'type'          => 'basic',
                'language_code' => 'en',
                'title'         => 'New Block',
                'region'        => 'region',
                'theme'         => 'theme',
                'weight'        => 10,
                'filter'        => ['+' => ['1/*']],
                'options'       => ['key' => 'value'],
                'body'          => 'Body',
                'custom_fields' => ['custom' => 'value'],
                'is_active'     => true,
                'is_cacheable'  => true
            ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'region'       => 'region',
                'theme'        => 'theme',
                'weight'       => 10,
                'filter'       => ['+' => ['1/*']],
                'options'      => ['key' => 'value'],
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'New Block',
                        'body'          => 'Body',
                        'custom_fields' => ['custom' => 'value'],
                        'is_active'     => true
                    ]
                ]
            ]
        );
    }

    public function canUpdateBlock(FunctionalTester $I)
    {
        $block = $I->haveBlock(
            [
                'type'         => 'basic',
                'region'       => 'region',
                'theme'        => 'theme',
                'weight'       => 10,
                'filter'       => 'filter',
                'options'      => 'options',
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'New Block',
                        'body'          => 'Body',
                        'custom_fields' => 'Custom fields',
                        'is_active'     => true
                    ]
                ]
            ]
        );

        $I->sendPATCH(apiUrl('blocks', ['id' => $block->id]),
            [
                'region'       => 'changed-region',
                'theme'        => 'changed-theme',
                'weight'       => 20,
                'filter'       => 'changed-filter',
                'options'      => 'changed-options',
                'is_active'    => false,
                'is_cacheable' => false
            ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'type'         => 'basic',
                'region'       => 'changed-region',
                'theme'        => 'changed-theme',
                'weight'       => 20,
                'filter'       => 'changed-filter',
                'options'      => 'changed-options',
                'is_active'    => false,
                'is_cacheable' => false,
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'New Block',
                        'body'          => 'Body',
                        'custom_fields' => 'Custom fields',
                        'is_active'     => true
                    ]
                ]
            ]
        );
    }

    public function canDeleteBlock(FunctionalTester $I)
    {
        $block = $I->haveBlock(
            [
                'type'         => 'basic',
                'region'       => 'region',
                'theme'        => 'theme',
                'weight'       => 10,
                'filter'       => 'filter',
                'options'      => 'options',
                'is_active'    => true,
                'is_cacheable' => true,
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'New Block',
                        'body'          => 'Body',
                        'custom_fields' => 'Custom fields',
                        'is_active'     => true
                    ]
                ]
            ]
        );

        $I->sendDELETE(apiUrl('blocks', ['id' => $block->id]));

        $I->seeResponseCodeIs(204);
    }

    public function shouldBeAbleToGetBlockTranslations(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $block = dispatch_now(CreateBlock::basic('Original Title', $en, $user, [
            'body'          => 'Original body',
            'custom_fields' => 'Original fields',
            'is_active'     => true
        ]));

        dispatch_now(new AddBlockTranslation($block, 'Modified title', $en, $user, [
            'body'          => 'Modified body',
            'custom_fields' => 'Modified fields',
            'is_active'     => true
        ]));

        dispatch_now(new AddBlockTranslation($block, 'Oryginalny Tytuł', $pl, $user, [
            'body'          => 'Oryginalna treść',
            'custom_fields' => 'Originalne pola',
            'is_active'     => true
        ]));

        dispatch_now(new AddBlockTranslation($block, 'Nowy Tytuł', $pl, $user, [
            'body'          => 'Nowa treść',
            'custom_fields' => 'Nowe pola',
            'is_active'     => true
        ]));

        $I->sendGET(apiUrl("blocks/$block->id/translations"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'language_code' => 'en',
                    'title'         => 'Original Title',
                    'body'          => 'Original body',
                    'custom_fields' => 'Original fields',
                    'is_active'     => false
                ],
                [
                    'language_code' => 'en',
                    'title'         => 'Modified title',
                    'body'          => 'Modified body',
                    'custom_fields' => 'Modified fields',
                    'is_active'     => true
                ],
                [
                    'language_code' => 'pl',
                    'title'         => 'Oryginalny Tytuł',
                    'body'          => 'Oryginalna treść',
                    'custom_fields' => 'Originalne pola',
                    'is_active'     => false
                ],
                [
                    'language_code' => 'pl',
                    'title'         => 'Nowy Tytuł',
                    'body'          => 'Nowa treść',
                    'custom_fields' => 'Nowe pola',
                    'is_active'     => true
                ]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfBlockTranslationsByLanguageCode(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $block = dispatch_now(CreateBlock::basic('Example Title', $en, $user));

        dispatch_now(new AddBlockTranslation($block, 'Przykładowy Tytuł', $pl, $user));

        $I->sendGET(apiUrl("blocks/$block->id/translations?language_code=en"));

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

        $I->sendGET(apiUrl("blocks/$block->id/translations?language_code=pl"));

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

    public function shouldBeAbleToFilterListOfBlockTranslationsByIsActive(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $block = dispatch_now(CreateBlock::basic('Original Title', $language, $user));

        dispatch_now(new AddBlockTranslation($block, 'Modified title', $language, $user));

        $I->sendGET(apiUrl("blocks/$block->id/translations?is_active=true"));

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

        $I->sendGET(apiUrl("blocks/$block->id/translations?is_active=false"));

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

    public function shouldBeAbleToFilterListOfBlockTranslationsByAuthorId(FunctionalTester $I)
    {
        $user1    = factory(User::class)->create();
        $user2    = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $block = dispatch_now(CreateBlock::basic('Example Title', $language, $user1));

        dispatch_now(new AddBlockTranslation($block, 'Translation from first user', $language, $user1));
        dispatch_now(new AddBlockTranslation($block, 'Translation from second user', $language, $user2));

        $I->sendGET(apiUrl("blocks/$block->id/translations?author_id=$user1->id"));

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

        $I->sendGET(apiUrl("blocks/$block->id/translations?author_id=$user2->id"));

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

    public function shouldBeAbleToFilterListOfBlockTranslationsByCreatedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4);
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $today       = Carbon::now()->format('Y-m-d');

        $block = $I->haveBlock([
            'type'         => 'basic',
            'created_at'   => $fourDaysAgo,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago blocks's content",
                    'is_active'     => true,
                    'created_at'    => $fourDaysAgo
                ]
            ]
        ]);

        $I->sendGET(apiUrl("blocks/$block->id/translations?created_at=$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl("blocks/$block->id/translations?created_at=!$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => "Four day's ago blocks's content"]
            ]
        );
    }

    public function shouldBeAbleToFilterListOfBlockTranslationsByUpdatedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4);
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $today       = Carbon::now()->format('Y-m-d');

        $block = $I->haveBlock([
            'type'         => 'basic',
            'created_at'   => $fourDaysAgo,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => "Four day's ago block's content",
                    'is_active'     => true,
                    'updated_at'    => $fourDaysAgo
                ]
            ]
        ]);

        $I->sendGET(apiUrl("blocks/$block->id/translations?updated_at=$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl("blocks/$block->id/translations?updated_at=!$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                ['title' => "Four day's ago block's content"]
            ]
        );
    }

    public function canCreateBlockTranslation(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $block = dispatch_now(CreateBlock::basic('Original Title', $language, $user));

        $I->sendPOST(apiUrl("blocks/$block->id/translations"),
            [
                'language_code' => 'en',
                'title'         => 'Example title',
                'body'          => 'Example body',
                'custom_fields' => ['example' => 'value'],
                'is_active'     => true
            ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'language_code' => 'en',
                'title'         => 'Example title',
                'body'          => 'Example body',
                'custom_fields' => ['example' => 'value']
            ]
        );

        $I->sendPOST(apiUrl("blocks/$block->id/translations"),
            [
                'language_code' => 'pl',
                'title'         => 'Nowy tytuł',
                'body'          => 'Nowa treść',
                'custom_fields' => ['nowe' => 'pole']
            ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'language_code' => 'pl',
                'title'         => 'Nowy tytuł',
                'body'          => 'Nowa treść',
                'custom_fields' => ['nowe' => 'pole']
            ]
        );
    }

    public function canDeleteInactiveBlockTranslation(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $block = dispatch_now(CreateBlock::basic('Example Title', $language, $user));

        $translation = dispatch_now(new AddBlockTranslation($block, 'Inactive Translation', $language, $user));

        dispatch_now(new AddBlockTranslation($block, 'New Active Title', $language, $user));

        $I->sendDELETE(apiUrl("blocks/$block->id/translations", ['translationId' => $translation->id]));

        $I->seeResponseCodeIs(204);
    }

    public function cantDeleteActiveBlockTranslation(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $block = dispatch_now(CreateBlock::basic('Example Title', $en, $user));

        $translation = dispatch_now(new AddBlockTranslation($block, 'Przykładowy Tytuł', $pl, $user));

        $I->sendDELETE(apiUrl("blocks/$block->id/translations", ['translationId' => $translation->id]));

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'message' => 'Cannot delete active translation'
            ]
        );
    }
}
