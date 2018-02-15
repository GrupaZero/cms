<?php namespace Cms\api;

use Cms\FunctionalTester;
use Gzero\Cms\Jobs\CreateBlock;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Core\Models\Language;

class ContentBlockCest {

    public function shouldBeAbleToGetAllBlocksDisplayedOnContent(FunctionalTester $I)
    {
        $user     = $I->haveUser();
        $language = new Language(['code' => 'en']);
        $content  = dispatch_now(CreateContent::content('Content title', $language, $user, [
            'body'      => 'Content body',
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Active block', $language, $user, [
            'region'    => 'sidebarLeft',
            'filter'    => ['+' => [$content->id . '/']],
            'body'      => 'Active block body',
            'is_active' => true,
        ]));

        dispatch_now(CreateBlock::basic('Inactive block', $language, $user, [
            'region'    => 'homepage',
            'filter'    => ['+' => [$content->id . '/']],
            'body'      => 'Inactive block body',
            'is_active' => false,
        ]));

        $I->apiLoginAsAdmin();
        $I->sendGet(apiUrl("contents/$content->id/blocks?language_code=en"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'region'       => 'sidebarLeft',
                    'filter'       => ['+' => [$content->id . '/']],
                    'translations' => [
                        'title' => 'Active block',
                        'body'  => 'Active block body'
                    ],
                    'is_active'    => true,
                ],
                [
                    'region'       => 'homepage',
                    'filter'       => ['+' => [$content->id . '/']],
                    'translations' => [
                        'title' => 'Inactive block',
                        'body'  => 'Inactive block body'
                    ],
                    'is_active'    => false,
                ]
            ]
        );
    }

    public function shouldBeAbleToGetOnlyActiveBlocksDisplayedOnContent(FunctionalTester $I)
    {
        $user     = $I->haveUser();
        $language = new Language(['code' => 'en']);
        $content  = dispatch_now(CreateContent::content('Content title', $language, $user, [
            'body'      => 'Content body',
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Active block', $language, $user, [
            'region'    => 'sidebarLeft',
            'filter'    => ['+' => [$content->id . '/']],
            'body'      => 'Active block body',
            'is_active' => true,
        ]));

        dispatch_now(CreateBlock::basic('Inactive block', $language, $user, [
            'region'    => 'homepage',
            'filter'    => ['+' => [$content->id . '/']],
            'body'      => 'Inactive block body',
            'is_active' => false,
        ]));

        $I->apiLoginAsAdmin();
        $I->sendGet(apiUrl("contents/$content->id/blocks?language_code=en&only_active=true"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'region'       => 'sidebarLeft',
                    'filter'       => ['+' => [$content->id . '/']],
                    'translations' => [
                        'title' => 'Active block',
                        'body'  => 'Active block body'
                    ],
                    'is_active'    => true,
                ]
            ]
        );
        $I->dontSeeResponseContainsJson(
            [
                [
                    'region'       => 'homepage',
                    'filter'       => ['+' => [$content->id . '/']],
                    'translations' => [
                        'title' => 'Inactive block',
                        'body'  => 'Inactive block body'
                    ],
                    'is_active'    => false,
                ]
            ]
        );
    }

}