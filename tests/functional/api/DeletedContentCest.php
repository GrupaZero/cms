<?php namespace Cms\api;

use Carbon\Carbon;
use Cms\FunctionalTester;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\DeleteContent;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class DeletedContentCest {

    public function _before(FunctionalTester $I)
    {
        $I->apiLoginAsAdmin();
    }

    public function shouldGetListOfDeletedContents(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $content = dispatch_now(CreateContent::content('Content Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('deleted-contents'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        dispatch_now(new DeleteContent($content));

        $I->sendGET(apiUrl('deleted-contents'));

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

    public function shouldBeAbleToFilterListOfDeletedContentsByDeletedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4)->format('Y-m-d');
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $today       = Carbon::now()->format('Y-m-d');

        $I->haveContents([
            [
                'deleted_at'   => $today,
                'translations' => [
                    ['language_code' => 'en', 'title' => "Today's deleted content"]
                ]
            ],
            [
                'deleted_at'   => $fourDaysAgo,
                'translations' => [
                    ['language_code' => 'en', 'title' => "Four day's ago deleted content"]
                ]
            ]
        ]);

        $I->sendGET(apiUrl("deleted-contents?deleted_at=$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            ['title' => "Today's deleted content"]
        );
        $I->dontSeeResponseContainsJson(
            ['title' => "Four day's ago deleted content"]);

        $I->sendGET(apiUrl("deleted-contents?deleted_at=$fourDaysAgo,$yesterday"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            ['title' => "Four day's ago deleted content"]);
        $I->dontSeeResponseContainsJson(
            ['title' => "Today's deleted content"]
        );
    }

    public function canDeleteOnlySoftDeletedContent(FunctionalTester $I)
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

        $I->sendDELETE(apiUrl('deleted-contents', ['id' => $content->id]));

        $I->seeResponseCodeIs(404);

        dispatch_now(new DeleteContent($content));

        $I->sendDELETE(apiUrl('deleted-contents', ['id' => $content->id]));

        $I->seeResponseCodeIs(204);
    }
}
