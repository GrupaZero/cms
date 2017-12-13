<?php namespace Cms\api;

use Carbon\Carbon;
use Cms\FunctionalTester;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class PublicContentCest {

    public function shouldGetListOfPublicContents(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content(
            'Content Title published one day ago',
            $language,
            $user,
            [
                'is_active' => true,
                'published_at' => Carbon::now()->subDay()
            ]
        ));

        dispatch_now(CreateContent::content(
            'Content Title published now',
            $language,
            $user,
            [
                'is_active' => true,
                'published_at' => Carbon::now()
            ]
        ));

        dispatch_now(CreateContent::content(
            'Content Title published tomorrow',
            $language,
            $user,
            [
                'is_active' => true,
                'published_at' => Carbon::tomorrow()
            ]
        ));

        $I->sendGET(apiUrl('public-contents'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->dontSeeResponseContainsJson(
            [
                [
                    'type'         => 'content',
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => 'Content Title published tomorrow',
                            'is_active'     => true,
                        ]
                    ]
                ]
            ]
        );
        $I->seeResponseContainsJson(
            [
                [
                    'type'         => 'content',
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => 'Content Title published one day ago',
                            'is_active'     => true,
                        ]
                    ]
                ],
                [
                    'type'         => 'content',
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => 'Content Title published now',
                            'is_active'     => true,
                        ]
                    ]
                ]
            ]
        );
    }
}
