<?php namespace Cms\api;

use Cms\FunctionalTester;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Jobs\CreateContent;
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
                    'parent_id'          => null,
                    'type'               => 'category',
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
                    'parent_id'    => $category->id,
                    'type'         => 'content',
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

    //public function shouldBeAbleToFilterListOfContentsByType(FunctionalTester $I)
    //{
    //    // @TODO 'Handle type relation';
    //
    //    $user     = factory(User::class)->create();
    //    $language = new Language(['code' => 'en']);
    //
    //    dispatch_now(CreateContent::category('Category Title', $language, $user, ['is_active' => true]));
    //    dispatch_now(CreateContent::content('Content Title', $language, $user, ['is_active' => true]));
    //
    //    $I->sendGET(apiUrl('contents?type=category'));
    //
    //    $I->seeResponseCodeIs(200);
    //    $I->seeResponseIsJson();
    //    $I->seeResponseJsonMatchesJsonPath('data[*]');
    //    $I->seeResponseContainsJson(
    //        [
    //            'type'         => 'category',
    //            'translations' => [
    //                [
    //                    'language_code' => 'en',
    //                    'title'         => 'Category Title',
    //                    'is_active'     => true,
    //                ]
    //            ]
    //        ]
    //    );
    //
    //    $I->sendGET(apiUrl('contents?type=!category'));
    //
    //    $I->seeResponseCodeIs(200);
    //    $I->seeResponseIsJson();
    //    $I->seeResponseJsonMatchesJsonPath('data[*]');
    //    $I->dontSeeResponseContainsJson(
    //        [
    //            'type'         => 'content',
    //            'translations' => [
    //                [
    //                    'language_code' => 'en',
    //                    'title'         => 'Content Title',
    //                    'is_active'     => true,
    //                ]
    //            ]
    //        ]
    //    );
    //}
}
