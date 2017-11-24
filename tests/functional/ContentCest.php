<?php namespace Cms;

use Codeception\Util\Locator;
use Gzero\Cms\Jobs\AddContentTranslation;
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

    public function canSeeTeaserAndBodyOnContentPage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('Content Title', new Language(['code' => 'en']), $user, [
            'teaser'    => 'Content teaser.',
            'body'      => 'Content body.',
            'is_active' => true
        ]));

        $I->amOnPage('content-title');
        $I->seeResponseCodeIs(200);
        $I->see('Content teaser.');
        $I->see('Content body.');
    }

    public function cantSeeUnpublishedContent(FunctionalTester $I)
    {
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('New Title', new Language(['code' => 'en']), $user));

        $I->amOnPage('new-title');
        $I->seeResponseCodeIs(404);
    }

    public function canSeeUnpublishedContentAsAdmin(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('Unpublished Content', new Language(['code' => 'en']), $user));

        $I->amOnPage('unpublished-content');
        $I->seeResponseCodeIs(200);
        $I->seeInTitle('Unpublished Content');
        $I->seeLink('Home');
        $I->seeElement('.breadcrumb');
        $I->see('This content is not published.', ['css' => 'div[role=alert]']);
    }

    public function canUseBreadcrumbs(FunctionalTester $I)
    {
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);
        $user = factory(User::class)->create();

        $root   = dispatch_now(CreateContent::category('Grandparent - Title', $en, $user, [
            'is_active' => true
        ]));
        $parent = dispatch_now(CreateContent::category('Parent - Title', $en, $user, [
            'parent_id' => $root->id,
            'is_active' => true
        ]));
        $child  = dispatch_now(CreateContent::content('Child - Title', $en, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));

        dispatch_now(new AddContentTranslation($root, 'Dziadek - Tytuł', $pl, $user));
        dispatch_now(new AddContentTranslation($parent, 'Rodzic - Tytuł', $pl, $user));
        dispatch_now(new AddContentTranslation($child, 'Dziecko - Tytuł', $pl, $user));

        $I->amOnPage('grandparent-title/parent-title/child-title');
        $I->seeResponseCodeIs(200);

        $I->see('Child - Title', ['css' => '.breadcrumb li.active']);
        $I->click('Parent - Title', '.breadcrumb li a');
        $I->seeCurrentUrlEquals('/grandparent-title/parent-title');

        $I->see('Parent - Title', ['css' => '.breadcrumb li.active']);
        $I->click('Grandparent - Title', '.breadcrumb li a');
        $I->seeCurrentUrlEquals('/grandparent-title');

        $I->see('Grandparent - Title', ['css' => '.breadcrumb li.active']);
        $I->click('Home', '.breadcrumb li a');
        $I->seeCurrentUrlEquals('/');

        $I->amOnPage('/pl/dziadek-tytul/rodzic-tytul/dziecko-tytul');
        $I->seeResponseCodeIs(200);

        $I->see('Dziecko - Tytuł', ['css' => '.breadcrumb li.active']);
        $I->click('Rodzic - Tytuł', '.breadcrumb li a');
        $I->seeCurrentUrlEquals('/pl/dziadek-tytul/rodzic-tytul');

        $I->see('Rodzic - Tytuł', ['css' => '.breadcrumb li.active']);
        $I->click('Dziadek - Tytuł', '.breadcrumb li a');
        $I->seeCurrentUrlEquals('/pl/dziadek-tytul');

        $I->see('Dziadek - Tytuł', ['css' => '.breadcrumb li.active']);
        $I->click('Strona główna', '.breadcrumb li a');
        $I->seeCurrentUrlEquals('/pl');
    }

    public function canUseChildrenLinksOnCategoryPage(FunctionalTester $I)
    {
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);
        $user = factory(User::class)->create();

        $root   = dispatch_now(CreateContent::category('Grandparent - Title', $en, $user, [
            'is_active' => true
        ]));
        $parent = dispatch_now(CreateContent::category('Parent - Title', $en, $user, [
            'parent_id' => $root->id,
            'is_active' => true
        ]));
        $child  = dispatch_now(CreateContent::content('Child - Title', $en, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));

        dispatch_now(new AddContentTranslation($root, 'Dziadek - Tytuł', $pl, $user));
        dispatch_now(new AddContentTranslation($parent, 'Rodzic - Tytuł', $pl, $user));
        dispatch_now(new AddContentTranslation($child, 'Dziecko - Tytuł', $pl, $user));

        $I->amOnPage('grandparent-title');
        $I->seeResponseCodeIs(200);
        $I->dontSeeLink('Child - Title', url('grandparent-title/parent-title/child-title'));

        $I->see('Grandparent - Title', ['css' => 'h1.content-title']);
        $I->seeLink('Read more', url('grandparent-title/parent-title'));
        $I->click('Parent - Title', ['css' => 'article .article-title a']);
        $I->seeCurrentUrlEquals('/grandparent-title/parent-title');

        $I->see('Parent - Title', ['css' => 'h1.content-title']);
        $I->seeLink('Read more', url('grandparent-title/parent-title/child-title'));
        $I->click('Child - Title', ['css' => 'article .article-title a']);
        $I->seeCurrentUrlEquals('/grandparent-title/parent-title/child-title');

        $I->amOnPage('/pl/dziadek-tytul');
        $I->seeResponseCodeIs(200);
        $I->dontSeeLink('Dziecko - Tytuł', url('/pl/dziadek-tytul/rodzic-tytul/dziecko-tytul'));

        $I->see('Dziadek - Tytuł', ['css' => 'h1.content-title']);
        $I->seeLink('Czytaj dalej', url('/pl/dziadek-tytul/rodzic-tytul'));
        $I->click('Rodzic - Tytuł', ['css' => 'article .article-title a']);
        $I->seeCurrentUrlEquals('/pl/dziadek-tytul/rodzic-tytul');

        $I->see('Rodzic - Tytuł', ['css' => 'h1.content-title']);
        $I->seeLink('Czytaj dalej', url('/pl/dziadek-tytul/rodzic-tytul/dziecko-tytul'));
        $I->click('Dziecko - Tytuł', ['css' => 'article .article-title a']);
        $I->seeCurrentUrlEquals('/pl/dziadek-tytul/rodzic-tytul/dziecko-tytul');
    }

    public function canSeeArticlesOnCategoryPage(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $user1    = factory(User::class)->create(['name' => 'Parent']);
        $user2    = factory(User::class)->create(['name' => 'Child']);
        $language = new Language(['code' => 'en']);

        $root   = dispatch_now(CreateContent::category('Grandparent - Title', $language, $user, [
            'teaser'    => 'Grandparent teaser',
            'is_active' => true
        ]));
        $parent = dispatch_now(CreateContent::category('Parent - Title', $language, $user1, [
            'teaser'    => 'Parent teaser',
            'parent_id' => $root->id,
            'is_active' => true
        ]));
        $child  = dispatch_now(CreateContent::content('Child - Title', $language, $user2, [
            'Child'     => 'Child teaser',
            'parent_id' => $parent->id,
            'is_active' => true
        ]));

        $I->amOnPage('grandparent-title');
        $I->seeResponseCodeIs(200);
        $I->see('Posted by Parent on ' . $parent->published_at->toDateTimeString(), ['css' => '.article-meta']);
        $I->see('Parent teaser');
        $I->dontSee('Posted by Child on ' . $child->published_at->toDateTimeString(), ['css' => '.article-meta']);
        $I->dontSee('Child teaser');
    }

    public function canSeeOrderedArticlesListOnCategoryPage(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent - Title', $language, $user, [
            'is_active' => true
        ]));
        dispatch_now(CreateContent::content('First - Title', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true,
            'weight'    => 0
        ]));
        dispatch_now(CreateContent::content('Second - Title', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true,
            'weight'    => 1
        ]));
        dispatch_now(CreateContent::content('Sticky - Title', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true,
            'is_sticky' => true,
            'weight'    => 10
        ]));
        dispatch_now(CreateContent::content('Promoted - Title', $language, $user, [
            'parent_id'   => $parent->id,
            'is_active'   => true,
            'is_promoted' => true,
            'weight'      => 20
        ]));

        $I->amOnPage('parent-title');
        $I->seeResponseCodeIs(200);

        $I->See('Promoted - Title', Locator::firstElement('article'));
        $I->See('Sticky - Title', Locator::elementAt('article', 2));
        $I->See('First - Title', Locator::elementAt('article', 3));
        $I->See('Second - Title', Locator::lastElement('article'));
    }
}
