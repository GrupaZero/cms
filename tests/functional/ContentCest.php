<?php namespace Cms;

use Gzero\Cms\Jobs\CreateContent;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Routing\Router;

class ContentCest {

    public function _before(FunctionalTester $I)
    {
        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/')->name('home-' . $language);
            $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
        });
    }

    public function canSeePublishedContent(FunctionalTester $I)
    {
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('Content Title', new Language(['code' => 'en']), $user, ['is_active' => true]));

        $I->amOnPage('content-title');
        $I->seeResponseCodeIs(200);
        $I->seeInTitle('Content Title');
        $I->seeLink('Home');
        $I->seeElement('.breadcrumb');
    }

    public function cantSeeUnpublishedContent(FunctionalTester $I)
    {
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('New Title', new Language(['code' => 'en']), $user));

        $I->amOnPage('new-title');
        $I->seeResponseCodeIs(404);
    }

    public function cantSeeUnpublishedContentAsAdmin(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('Unpublished Content', new Language(['code' => 'en']), $user));

        $I->amOnPage('unpublished-content');
        $I->seeResponseCodeIs(200);
        $I->seeInTitle('Unpublished Content');
        $I->seeLink('Home');
        $I->seeElement('.breadcrumb');
    }

}
