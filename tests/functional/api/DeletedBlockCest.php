<?php namespace Cms\api;

use Carbon\Carbon;
use Cms\FunctionalTester;
use Gzero\Cms\Jobs\CreateBlock;
use Gzero\Cms\Jobs\DeleteBlock;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class DeletedBlockCest {

    public function _before(FunctionalTester $I)
    {
        $I->apiLoginAsAdmin();
    }

    public function shouldGetListOfDeletedBlocks(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $block = dispatch_now(CreateBlock::basic('Block Title', $language, $user, ['is_active' => true]));

        $I->sendGET(apiUrl('deleted-blocks'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        dispatch_now(new DeleteBlock($block));

        $I->sendGET(apiUrl('deleted-blocks'));

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

    public function shouldBeAbleToFilterListOfDeletedBlocksByDeletedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4)->format('Y-m-d');
        $yesterday   = Carbon::yesterday()->format('Y-m-d');
        $today       = Carbon::now()->format('Y-m-d');

        $I->haveBlock(['deleted_at'   => $today,
                       'translations' => [['language_code' => 'en', 'title' => "Today's deleted block"]]
        ]);
        $I->haveBlock([
            'deleted_at'   => $fourDaysAgo,
            'translations' => [['language_code' => 'en', 'title' => "Four day's ago deleted block"]]
        ]);

        $I->sendGET(apiUrl("deleted-blocks?deleted_at=$yesterday,$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            ['title' => "Today's deleted block"]
        );
        $I->dontSeeResponseContainsJson(
            ['title' => "Four day's ago deleted block"]);

        $I->sendGET(apiUrl("deleted-blocks?deleted_at=$fourDaysAgo,$yesterday"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            ['title' => "Four day's ago deleted block"]);
        $I->dontSeeResponseContainsJson(
            ['title' => "Today's deleted block"]
        );
    }

    public function canDeleteOnlySoftDeletedBlock(FunctionalTester $I)
    {
        $block = $I->haveBlock([
            'type'         => 'basic',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Some Title',
                    'body'          => 'Body',
                ]
            ]
        ]);

        $I->sendDELETE(apiUrl('deleted-blocks', ['id' => $block->id]));

        $I->seeResponseCodeIs(404);

        dispatch_now(new DeleteBlock($block));

        $I->sendDELETE(apiUrl('deleted-blocks', ['id' => $block->id]));

        $I->seeResponseCodeIs(204);
    }

    public function canRestoreOnlySoftDeletedBlock(FunctionalTester $I)
    {
        $block = $I->haveBlock([
            'translations' => [
                ['language_code' => 'en', 'title' => 'Restored block',]
            ]
        ]);

        $I->sendPOST(apiUrl("deleted-blocks/$block->id/restore"));

        $I->seeResponseCodeIs(404);

        dispatch_now(new DeleteBlock($block));

        $I->sendPOST(apiUrl("deleted-blocks/$block->id/restore"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            ['title' => 'Restored block']);
    }
}
