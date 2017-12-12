<?php namespace Cms\api;

use Carbon\Carbon;
use Cms\FunctionalTester;

class SecurityCest {

    public function guestShouldNotBeAllowedToGetContents(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('contents'));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToCreateContent(FunctionalTester $I)
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


        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToGetSingleContent(FunctionalTester $I)
    {
        $content = $I->haveContent(['theme' => 'is-content']);

        $I->sendGET(apiUrl('contents', ['id' => $content->id]));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToUpdateContent(FunctionalTester $I)
    {
        $content = $I->haveContent();

        $I->sendPATCH(apiUrl('contents', ['id' => $content->id]), ['rating' => 10]);

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToGetDeletedContents(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('deleted-contents'));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToGetCategoryChildren(FunctionalTester $I)
    {
        $category = $I->haveContent(['type' => 'category']);

        $I->sendGET(apiUrl("contents/$category->id/children"));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }
}
