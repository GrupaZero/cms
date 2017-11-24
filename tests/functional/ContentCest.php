<?php namespace Cms;

use Carbon\Carbon;
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

        $content = dispatch_now(CreateContent::content('Content Title', new Language(['code' => 'en']), $user, [
            'teaser'    => 'Content teaser.',
            'body'      => 'Content body.',
            'is_active' => true
        ]));

        $I->amOnPage('content-title');
        $I->seeResponseCodeIs(200);
        $I->see($content->translations->first()->teaser);
        $I->see($content->translations->first()->body);
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
    }

    public function canSeeInfoAboutUnpublishedContentAsAdmin(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('Unpublished Content', new Language(['code' => 'en']), $user));

        $I->amOnPage('unpublished-content');
        $I->seeResponseCodeIs(200);
        $I->see('This content is not published.', ['css' => 'div[role=alert]']);
    }

    public function canSeeBreadcrumbsInDefaultLanguage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent Title', $language, $user, [
            'is_active' => true
        ]));
        $subParent = dispatch_now(CreateContent::category('Sub parent Title', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));
        dispatch_now(CreateContent::content('Child Title', $language, $user, [
            'parent_id' => $subParent->id,
            'is_active' => true
        ]));

        $I->amOnPage('parent-title/sub-parent-title/child-title');
        $I->seeResponseCodeIs(200);
        $I->seeElement('.breadcrumb');
        $I->seeLink('Home', url('/'));
        $I->seeLink('Parent Title', url('parent-title'));
        $I->seeLink('Sub parent Title', url('parent-title/sub-parent-title'));
        $I->see('Child Title', ['css' => 'li.active']);
    }

    public function canSeeBreadcrumbsInNonDefaultLanguage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'pl']);

        $parent = dispatch_now(CreateContent::category('Tytuł rodzic', $language, $user, [
            'is_active' => true
        ]));
        $subParent = dispatch_now(CreateContent::category('Podtytuł rodzic', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));
        dispatch_now(CreateContent::content('Tytuł dziecko', $language, $user, [
            'parent_id' => $subParent->id,
            'is_active' => true
        ]));

        $I->amOnPage('pl/tytul-rodzic/podtytul-rodzic/tytul-dziecko');
        $I->seeResponseCodeIs(200);
        $I->seeElement('.breadcrumb');
        $I->seeLink('Strona główna', url('pl'));
        $I->seeLink('Tytuł rodzic', url('pl/tytul-rodzic'));
        $I->seeLink('Podtytuł rodzic', url('pl/tytul-rodzic/podtytul-rodzic'));
        $I->see('Tytuł dziecko', ['css' => 'li.active']);
    }

    public function canSeeLinksToChildrenOnCategoryPageInDefaultLanguage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent Title', $language, $user, [
            'is_active' => true
        ]));
        $subParent = dispatch_now(CreateContent::category('Sub parent Title', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));
        dispatch_now(CreateContent::content('Child Title', $language, $user, [
            'parent_id' => $subParent->id,
            'is_active' => true
        ]));

        $I->amOnPage('parent-title');
        $I->seeResponseCodeIs(200);
        $I->see('Parent Title', ['css' => 'h1.content-title']);
        $I->seeLink('Sub parent Title', url('parent-title/sub-parent-title'));
        $I->dontSeeLink('Child Title', url('parent-title/sub-parent-title/child-title'));
    }

    public function canSeeLinksToChildrenOnCategoryPageInNonDefaultLanguage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'pl']);

        $parent = dispatch_now(CreateContent::category('Tytuł rodzic', $language, $user, [
            'is_active' => true
        ]));
        $subParent = dispatch_now(CreateContent::category('Podtytuł rodzic', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));
        dispatch_now(CreateContent::content('Tytuł dziecko', $language, $user, [
            'parent_id' => $subParent->id,
            'is_active' => true
        ]));

        $I->amOnPage('pl/tytul-rodzic');
        $I->seeResponseCodeIs(200);
        $I->see('Tytuł rodzic', ['css' => 'h1.content-title']);
        $I->seeLink('Podtytuł rodzic', url('pl/tytul-rodzic/podtytul-rodzic'));
        $I->dontSeeLink('Tytuł dziecko', url('pl/tytul-rodzic/podtytul-rodzic/tytul-dziecko'));
    }

    public function canSeeArticleMetaOnCategoryPage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent Title', $language, $user, [
            'is_active' => true,
            'published_at' => Carbon::yesterday()
        ]));
        $subParent = dispatch_now(CreateContent::category('Sub parent Title', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true,
            'published_at' => Carbon::now()
        ]));
        $child = dispatch_now(CreateContent::content('Child Title', $language, $user, [
            'parent_id' => $subParent->id,
            'is_active' => true,
            'published_at' => Carbon::tomorrow()
        ]));

        $I->amOnPage('parent-title');
        $I->seeResponseCodeIs(200);
        $I->seeElement('.article-meta');
        $I->dontSee('Posted by ' . $parent->author->name . ' on ' . $parent->published_at->toDateTimeString(),
            ['css' => '.article-meta']);
        $I->see('Posted by ' . $subParent->author->name . ' on ' . $subParent->published_at->toDateTimeString(),
            ['css' => '.article-meta']);
        $I->dontSee('Posted by ' . $child->author->name . ' on ' . $child->published_at->toDateTimeString(),
            ['css' => '.article-meta']);
    }

    public function canSeeTeasersOnCategoryPage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent Title', $language, $user, [
            'teaser' => 'Parent title teaser',
            'is_active' => true
        ]));
        $subParent = dispatch_now(CreateContent::category('Sub parent Title', $language, $user, [
            'teaser' => 'Sub parent title teaser',
            'parent_id' => $parent->id,
            'is_active' => true
        ]));
        $child = dispatch_now(CreateContent::content('Child Title', $language, $user, [
            'teaser' => 'Child title teaser',
            'parent_id' => $subParent->id,
            'is_active' => true
        ]));

        $I->amOnPage('parent-title');
        $I->seeResponseCodeIs(200);
        $I->see($parent->translations->first()->teaser);
        $I->see($subParent->translations->first()->teaser);
        $I->dontSee($child->translations->first()->teaser);
    }

    public function canSeeReadMoreLinkOnCategoryPageInDefaultLanguage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent Title', $language, $user, [
            'is_active' => true
        ]));
        $subParent = dispatch_now(CreateContent::category('Sub parent Title', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));
        dispatch_now(CreateContent::content('Child Title', $language, $user, [
            'parent_id' => $subParent->id,
            'is_active' => true
        ]));

        $I->amOnPage('parent-title');
        $I->seeResponseCodeIs(200);
        $I->seeLink('Read more', url('parent-title'));
        $I->seeLink('Read more', url('parent-title/sub-parent-title'));
        $I->dontSeeLink('Read more', url('parent-title/sub-parent-title/child-title'));
    }

    public function canSeeReadMoreLinkOnCategoryPageInNonDefaultLanguage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'pl']);

        $parent = dispatch_now(CreateContent::category('Tytuł rodzic', $language, $user, [
            'is_active' => true
        ]));
        $subParent = dispatch_now(CreateContent::category('Podtytuł rodzic', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));
        dispatch_now(CreateContent::content('Tytuł dziecko', $language, $user, [
            'parent_id' => $subParent->id,
            'is_active' => true
        ]));

        $I->amOnPage('pl/tytul-rodzic');
        $I->seeResponseCodeIs(200);
        $I->seeLink('Czytaj dalej', url('pl/tytul-rodzic'));
        $I->seeLink('Czytaj dalej', url('pl/tytul-rodzic/podtytul-rodzic'));
        $I->dontSeeLink('Czytaj dalej', url('pl/tytul-rodzic/podtytul-rodzic/tytul-dziecko'));
    }

    public function canSeeStickyContentAtTopOfTheCategoryPage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent Title', $language, $user, [
            'is_active' => true
        ]));
        $child1 = dispatch_now(CreateContent::content('Child Title 1', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));
        $child2sticky = dispatch_now(CreateContent::content('Sticky child Title 2', $language, $user, [
            'parent_id' => $parent->id,
            'is_sticky' => true,
            'is_active' => true
        ]));
        $child3 = dispatch_now(CreateContent::content('Child Title 3', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));

        $I->amOnPage('parent-title');
        $I->seeResponseCodeIs(200);
        $I->see('Child Title 1', ['css' => 'article']);
        $I->see('Sticky child Title 2', ['css' => 'article.is-sticky']);
        $I->see('Child Title 3', ['css' => 'article']);
    }
}
