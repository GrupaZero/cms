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

    public function guestShouldNotBeAllowedToCreateContentTranslations(FunctionalTester $I)
    {
        $content = $I->haveContent();

        $I->sendPOST(apiUrl("contents/$content->id/translations"), ['language_code' => 'pl',]);

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToGetContentTranslations(FunctionalTester $I)
    {
        $content = $I->haveContent();

        $I->sendGET(apiUrl("contents/$content->id/translations"));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToGetDeletedContent(FunctionalTester $I)
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

    public function guestShouldNotBeAllowedToDeletedContent(FunctionalTester $I)
    {
        $content = $I->haveContent();

        $I->sendDELETE(apiUrl('contents', ['id' => $content->id]));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToRestoreDeletedContent(FunctionalTester $I)
    {
        $content = $I->haveContent(['deleted_at' => date('Y-m-d H:i:s')]);

        $I->sendPOST(apiUrl("deleted-contents/$content->id/restore"));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToDeletedContentTranslations(FunctionalTester $I)
    {
        $content = $I->haveContent([
            'translations' => [
                ['language_code' => 'en', 'title' => 'Example', 'is_active' => true]
            ]
        ]);

        $I->sendDELETE(apiUrl("contents/$content->id/translations", ['id' => $content->translations->first()->id]));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToGetBlocks(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('blocks'));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToCreateBlock(FunctionalTester $I)
    {
        $I->sendPOST(apiUrl('blocks'),
            [
                'type'          => 'basic',
                'language_code' => 'en',
                'title'         => 'Example Title',
                'body'          => 'Example Body',
                'weight'        => 10,
                'theme'         => 'is-content',
                'region'        => 'sidebarLeft',
            ]);
        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToGetSingleBlock(FunctionalTester $I)
    {
        $block = $I->haveBlock();

        $I->sendGET(apiUrl('blocks', ['id' => $block->id]));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToDeletedBlock(FunctionalTester $I)
    {
        $block = $I->haveBlock();

        $I->sendDELETE(apiUrl('blocks', ['id' => $block->id]));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToUpdateBlock(FunctionalTester $I)
    {
        $block = $I->haveBlock();

        $I->sendPATCH(apiUrl('blocks', ['id' => $block->id]), ['region' => 'sidebarLeft']);

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToGetBlockTranslations(FunctionalTester $I)
    {
        $block = $I->haveBlock();

        $I->sendGET(apiUrl("blocks/$block->id/translations"));

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToCreateNewBlockTranslations(FunctionalTester $I)
    {
        $block = $I->haveBlock();

        $I->sendPOST(apiUrl("blocks/$block->id/translations"), ['language_code' => 'pl',]);

        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function guestShouldNotBeAllowedToDeletedBlockTranslations(FunctionalTester $I)
    {
        $block = $I->haveBlock([
            'translations' => [
                ['language_code' => 'en', 'is_active' => true]
            ]
        ]);

        $I->sendDELETE(apiUrl("blocks/$block->id/translations", ['id' => $block->translations->first()->id]));
        $I->seeResponseCodeIs(401);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

}
