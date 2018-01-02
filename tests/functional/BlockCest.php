<?php namespace Cms;

use Carbon\Carbon;
use Gzero\Cms\Jobs\CreateBlock;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Routing\Router;

class BlockCest {

    public function _before(FunctionalTester $I)
    {
        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return view('gzero-core::layouts.withRegions');
            })->name('home-' . $language);
            $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
        });
    }

    public function canSeeBlockInFooter(FunctionalTester $I)
    {
        $user = factory(User::class)->create();

        dispatch_now(CreateBlock::basic('Block Title', new Language(['code' => 'en']), $user, [
            'is_active' => true,
            'region'    => 'footer'
        ]));

        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->see('Block Title');
    }
}
