<?php namespace Cms;

use Carbon\Carbon;
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
            $router->get('/', 'Gzero\Cms\Http\Controllers\HomeController@index')
                ->middleware('web')
                ->name(mlSuffix('home', $language));
        });

        $I->haveCatchAllRoute(function ($router) {
            $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')
                ->middleware('web')
                ->where('path', '.*');
        });
    }

    public function canSeePublishedContent(FunctionalTester $I)
    {
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('Content Title', new Language(['code' => 'en']), $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

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
            'published_at' => Carbon::now(),
            'teaser'       => 'Content teaser.',
            'body'         => 'Content body.',
            'is_active'    => true
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

    public function cantSeeContentPublishedInTheFuture(FunctionalTester $I)
    {
        $user = factory(User::class)->create();

        dispatch_now(CreateContent::content('Content Title', new Language(['code' => 'en']), $user, [
            'published_at' => Carbon::now()->addDay(),
            'is_active'    => true
        ]));

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
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));
        $parent = dispatch_now(CreateContent::category('Parent - Title', $en, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $root->id,
            'is_active'    => true
        ]));
        $child  = dispatch_now(CreateContent::content('Child - Title', $en, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'is_active'    => true
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
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));
        $parent = dispatch_now(CreateContent::category('Parent - Title', $en, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $root->id,
            'is_active'    => true
        ]));
        $child  = dispatch_now(CreateContent::content('Child - Title', $en, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'is_active'    => true
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
            'teaser'       => 'Grandparent teaser',
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));
        $parent = dispatch_now(CreateContent::category('Parent - Title', $language, $user1, [
            'teaser'       => 'Parent teaser',
            'parent_id'    => $root->id,
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));
        $child  = dispatch_now(CreateContent::content('Child - Title', $language, $user2, [
            'Child'        => 'Child teaser',
            'parent_id'    => $parent->id,
            'published_at' => Carbon::now(),
            'is_active'    => true
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
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));
        dispatch_now(CreateContent::content('First - Title', $language, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'is_active'    => true,
            'weight'       => 0
        ]));
        dispatch_now(CreateContent::content('Second - Title', $language, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'is_active'    => true,
            'weight'       => 1
        ]));
        dispatch_now(CreateContent::content('Sticky - Title', $language, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'is_active'    => true,
            'is_sticky'    => true,
            'weight'       => 10
        ]));
        dispatch_now(CreateContent::content('Promoted - Title', $language, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'is_active'    => true,
            'is_promoted'  => true,
            'weight'       => 20
        ]));

        $I->amOnPage('parent-title');
        $I->seeResponseCodeIs(200);

        $I->see('Promoted - Title', Locator::firstElement('article'));
        $I->see('Sticky - Title', Locator::elementAt('article', 2));
        $I->see('First - Title', Locator::elementAt('article', 3));
        $I->see('Second - Title', Locator::lastElement('article'));
    }

    public function cantSeeUnpublishedArticlesOnCategoryPage(FunctionalTester $I)
    {
        $user     = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent - Title', $language, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::content('Published - Title', $language, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::content('Unpublished - Title', $language, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'is_active'    => false
        ]));

        dispatch_now(CreateContent::content('Future - Title', $language, $user, [
            'published_at' => Carbon::now()->addDays(1),
            'parent_id'    => $parent->id,
            'is_active'    => true
        ]));

        $I->amOnPage('parent-title');
        $I->seeResponseCodeIs(200);

        $I->see('Published - Title', '.article-title');
        $I->dontSee('Unpublished - Title', '.article-title');
        $I->dontSee('Future - Title', '.article-title');
    }

    public function canSeeStDataMarkupWithShareLogo(FunctionalTester $I)
    {
        $user       = factory(User::class)->create(['name' => 'John Doe']);
        $thumbWidth = config('gzero-cms.image.thumb.width');

        $content = dispatch_now(CreateContent::content('Content Title', new Language(['code' => 'en']), $user, [
            'published_at' => Carbon::now(),
            'teaser'       => 'Content teaser.',
            'body'         => 'Content body.',
            'is_active'    => true
        ]));

        $content = $content->fresh();
        $tag     = 'script[type="application/ld+json"]';

        $I->amOnPage('content-title');
        $I->seeResponseCodeIs(200);
        $I->see('"@context": "http://schema.org"', $tag);
        $I->see('"@type": "Article"', $tag);
        $I->see('"headline": "Content Title"', $tag);
        $I->see('"url": "'.url('content-title').'"', $tag);
        $I->see('"datePublished": "' . $content->published_at->toDateTimeString() . '"', $tag);
        $I->see('"dateModified": "' . $content->updated_at->toDateTimeString() . '"', $tag);

        $I->see('"author": 
        {
                "@type": "Person",
                "name": "John Doe"
        }', $tag);

        $I->see('"publisher": 
        {
            "@type": "Organization",
            "url": "'.url('/').'",
            "name": "' . config('app.name') . '",
            "logo": {
                "@type": "ImageObject",
                "url": "'.url('images/logo.png').'"
            }
        }', $tag);

        $I->see('"mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "'.url('/').'"
        }', $tag);

        $I->see('"image": {
            "@type": "ImageObject",
            "url": "'.url('images/share-logo.png').'",
            "width": "' . $thumbWidth . '",
            "height": "auto"
        }', $tag);
    }

    public function canSeeStDataMarkupWithAncestorsNames(FunctionalTester $I)
    {
        $user     = factory(User::class)->create(['name' => 'John Doe']);
        $language = new Language(['code' => 'en']);

        $root = dispatch_now(CreateContent::category('Offer', $language, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        $parent = dispatch_now(CreateContent::category('Category', $language, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $root->id,
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::content('Content Title', $language, $user, [
            'published_at' => Carbon::now(),
            'parent_id'    => $parent->id,
            'teaser'       => 'Content teaser.',
            'body'         => 'Content body.',
            'is_active'    => true
        ]));

        $I->amOnPage('offer/category/content-title');
        $I->seeResponseCodeIs(200);
        $I->see('"articleSection": "Offer,Category', 'script[type="application/ld+json"]');
    }

    public function canSeeStDataMarkupWithFirstImageUrlFromTeaser(FunctionalTester $I)
    {
        $user       = factory(User::class)->create(['name' => 'John Doe']);
        $language   = new Language(['code' => 'en']);
        $thumbWidth = config('gzero-cms.image.thumb.width');

        dispatch_now(CreateContent::content('Content Title', $language, $user, [
            'published_at' => Carbon::now(),
            'teaser'       => 'Content teaser. <img src="http://dev.gzero.pl/images/first-image.png" class="img-fluid">',
            'body'         => 'Content body.',
            'is_active'    => true
        ]));

        $I->amOnPage('content-title');
        $I->seeResponseCodeIs(200);
        $I->see('"image": {
            "@type": "ImageObject",
            "url": "http://dev.gzero.pl/images/first-image.png",
            "width": "' . $thumbWidth . '",
            "height": "auto"
        }', 'script[type="application/ld+json"]');
    }

    public function canSeeStDataMarkupWithItemListTypeOnCategoryPage(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $language = new Language(['code' => 'en']);

        $root = dispatch_now(CreateContent::category('Parent - Title', $language, $user, [
            'teaser'       => 'Parent - Title',
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));
        dispatch_now(CreateContent::category('Child 1 - Title', $language, $user, [
            'teaser'       => 'Child 1 - Title',
            'parent_id'    => $root->id,
            'published_at' => Carbon::now()->subDay(),
            'is_active'    => true
        ]));
        dispatch_now(CreateContent::content('Child 2 - Title', $language, $user, [
            'Child'        => 'Child 2 teaser',
            'parent_id'    => $root->id,
            'published_at' => Carbon::now()->subDays(2),
            'is_active'    => true
        ]));
        dispatch_now(CreateContent::content('Child 3 - Title', $language, $user, [
            'Child'        => 'Child 3 teaser',
            'parent_id'    => $root->id,
            'published_at' => Carbon::now()->subDays(3),
            'is_active'    => true
        ]));
        dispatch_now(CreateContent::content('Child not published - Title', $language, $user, [
            'Child'        => 'Child not published - Title',
            'parent_id'    => $root->id,
            'published_at' => Carbon::now()->addDay(),
            'is_active'    => true
        ]));

        $I->amOnPage('parent-title');

        $tag = 'script[type="application/ld+json"]';

        $I->see('
            "@type":"ItemList",
            "itemListElement":[{
                "@type": "ListItem",
                "position": 1,
                "url":"'.url('parent-title/child-1-title').'"
            },{
                "@type": "ListItem",
                "position": 2,
                "url":"'.url('parent-title/child-2-title').'"
            },{
                "@type": "ListItem",
                "position": 3,
                "url":"'.url('parent-title/child-3-title').'"
            }]
        ', $tag);
        $I->dontSee('"url":"'.url('parent-title/child-not-published-title').'"', $tag);
        $I->see('Child 1 - Title', Locator::firstElement('article'));
        $I->see('Child 2 - Title', Locator::elementAt('article', 2));
        $I->see('Child 3 - Title', Locator::lastElement('article'));
    }
}
