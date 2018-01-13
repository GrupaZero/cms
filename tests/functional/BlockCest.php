<?php namespace Cms;

use Carbon\Carbon;
use Gzero\Cms\Jobs\AddBlockTranslation;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Jobs\CreateBlock;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Core\Jobs\CreateFile;
use Gzero\Core\Jobs\SyncFiles;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Storage;

class BlockCest {

    public function _before(FunctionalTester $I)
    {
        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', 'Gzero\Cms\Http\Controllers\HomeController@index')->name(mlSuffix('home', $language));
            $router->get('{path?}', 'Gzero\Core\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
        });
    }

    public function shouldLoadTranslationOnLaravelRoutes(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $block = dispatch_now(CreateBlock::basic('Block title', $en, $user, [
            'body'      => 'Block body',
            'region'    => 'homepage',
            'is_active' => true
        ]));

        dispatch_now(new AddBlockTranslation($block, 'Nowy blok', $pl, $user,
            [
                'body'          => 'Treść bloku',
                'custom_fields' => 'Custom Fields'
            ]
        ));

        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->see('Block title', '#homepage-region .block-title');
        $I->see('Block body', '#homepage-region .block-body');

        $I->amOnPage('/pl');
        $I->seeResponseCodeIs(200);
        $I->see('Nowy blok', '#homepage-region .block-title');
        $I->see('Treść bloku', '#homepage-region .block-body');
    }

    public function shouldLoadTranslationOnDynamicRoutes(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $content = dispatch_now(CreateContent::content('Example', $en, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));
        dispatch_now(new AddContentTranslation($content, 'Przykład', $pl, $user, ['is_active' => true]));

        $block = dispatch_now(CreateBlock::basic('Block title', $en, $user, [
            'body'      => 'Block body',
            'region'    => 'sidebarLeft',
            'is_active' => true
        ]));

        dispatch_now(new AddBlockTranslation($block, 'Nowy blok', $pl, $user,
            ['body' => 'Treść bloku']
        ));

        $I->amOnPage('example');
        $I->seeResponseCodeIs(200);
        $I->see('Block title', '#sidebarLeft .block-title');
        $I->see('Block body', '#sidebarLeft .block-body');

        $I->amOnPage('/pl/przyklad');
        $I->seeResponseCodeIs(200);
        $I->see('Nowy blok', '#sidebarLeft .block-title');
        $I->see('Treść bloku', '#sidebarLeft .block-body');
    }

    public function shouldRenderBlocksInAllAvailableRegionsOnDynamicRoutes(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content('Example', $en, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateBlock::basic('Header', $en, $user, [
            'body'      => 'Block in header region',
            'region'    => 'header',
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Featured', $en, $user, [
            'body'      => 'Block in featured region',
            'region'    => 'featured',
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Content Header', $en, $user, [
            'body'      => 'Block in contentHeader region',
            'region'    => 'contentHeader',
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar left', $en, $user, [
            'body'      => 'Block in sidebar left region',
            'region'    => 'sidebarLeft',
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar right', $en, $user, [
            'body'      => 'Block in sidebar right region',
            'region'    => 'sidebarRight',
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Content Footer', $en, $user, [
            'body'      => 'Block in contentFooter region',
            'region'    => 'contentFooter',
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Footer', $en, $user, [
            'body'      => 'Block in footer region',
            'region'    => 'footer',
            'is_active' => true
        ]));

        $I->amOnPage('example');
        $I->seeResponseCodeIs(200);
        $I->see('Header', '#header-region .block-title');
        $I->see('Block in header region', '#header-region .block-body');

        $I->see('Featured', '#featured-region .block-title');
        $I->see('Block in featured region', '#featured-region .block-body');

        $I->see('Content Header', '#content-header-region .block-title');
        $I->see('Block in contentHeader region', '#content-header-region .block-body');

        $I->see('Sidebar left', '#sidebarLeft .block-title');
        $I->see('Block in sidebar left region', '#sidebarLeft .block-body');

        $I->see('Sidebar right', '#sidebarRight .block-title');
        $I->see('Block in sidebar right region', '#sidebarRight .block-body');

        $I->see('Content Footer', '#content-footer-region .block-title');
        $I->see('Block in contentFooter region', '#content-footer-region .block-body');

        $I->see('Footer', '#footer-region .block-title');
        $I->see('Block in footer region', '#footer-region .block-body');
    }

    public function shouldRenderBlockTheme(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);

        dispatch_now(CreateContent::content('Example', $en, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateBlock::basic('Block with theme class', $en, $user, [
            'theme'     => 'my-block',
            'region'    => 'header',
            'is_active' => true
        ]));

        $I->amOnPage('example');
        $I->seeResponseCodeIs(200);
        $I->see('Block with theme class', '#header-region .my-block');
    }

    public function shouldNotBeAbleToSeeFilteredBlocksOnLaravelRoutes(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);

        dispatch_now(CreateBlock::basic('Sidebar left', $en, $user, [
            'body'      => 'Block in sidebar left region',
            'region'    => 'sidebarLeft',
            'filter'    => ['+' => ['home-en']],
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar right', $en, $user, [
            'body'      => 'Block in sidebar right region',
            'region'    => 'sidebarRight',
            'filter'    => ['-' => ['home-en']],
            'is_active' => true
        ]));

        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->see('Sidebar left', '#sidebarLeft .block-title');
        $I->see('Block in sidebar left region', '#sidebarLeft .block-body');
        $I->dontSee('Sidebar right', '#sidebarRight .block-title');
        $I->dontSee('Block in sidebar right region', '#sidebarRight .block-body');
    }

    public function shouldNotBeAbleToSeeFilteredBlocksOnDynamicRoutes(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);

        $content = dispatch_now(CreateContent::content('Example', $en, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar left', $en, $user, [
            'body'      => 'Block in sidebar left region',
            'region'    => 'sidebarLeft',
            'filter'    => ['+' => [$content->id . '/']],
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar right', $en, $user, [
            'body'      => 'Block in sidebar right region',
            'region'    => 'sidebarRight',
            'filter'    => ['-' => [$content->id . '/']],
            'is_active' => true
        ]));

        $I->amOnPage('example');
        $I->seeResponseCodeIs(200);
        $I->see('Sidebar left', '#sidebarLeft .block-title');
        $I->see('Block in sidebar left region', '#sidebarLeft .block-body');
        $I->dontSee('Sidebar right', '#sidebarRight .block-title');
        $I->dontSee('Block in sidebar right region', '#sidebarRight .block-body');
    }

    public function shouldNotBeAbleToSeeFilteredBlocksOnNestedDynamicRoutes(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);

        $parent = dispatch_now(CreateContent::category('Parent', $en, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::content('Content', $en, $user, [
            'parent_id'    => $parent->id,
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar left', $en, $user, [
            'body'      => 'Block in sidebar left region',
            'region'    => 'sidebarLeft',
            'filter'    => ['+' => [$parent->id . '/*']],
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar right', $en, $user, [
            'body'      => 'Block in sidebar right region',
            'region'    => 'sidebarRight',
            'filter'    => ['-' => [$parent->id . '/*']],
            'is_active' => true
        ]));

        $I->amOnPage('parent/content');
        $I->seeResponseCodeIs(200);
        $I->see('Sidebar left', '#sidebarLeft .block-title');
        $I->see('Block in sidebar left region', '#sidebarLeft .block-body');
        $I->dontSee('Sidebar right', '#sidebarRight .block-title');
        $I->dontSee('Block in sidebar right region', '#sidebarRight .block-body');
    }

    public function shouldNotBeAbleToSeeFilteredBlocksOnDescendantsRoutes(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);

        $root = dispatch_now(CreateContent::category('Root', $en, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        $parent = dispatch_now(CreateContent::category('Parent', $en, $user, [
            'parent_id'    => $root->id,
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateContent::content('Content', $en, $user, [
            'parent_id'    => $parent->id,
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar left', $en, $user, [
            'body'      => 'Block in sidebar left region',
            'region'    => 'sidebarLeft',
            'filter'    => ['+' => [$root->id . '/*']],
            'is_active' => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar right', $en, $user, [
            'body'      => 'Block in sidebar right region',
            'region'    => 'sidebarRight',
            'filter'    => ['-' => [$root->id . '/' . $parent->id . '/*']],
            'is_active' => true
        ]));

        $I->amOnPage('root/parent/content');
        $I->seeResponseCodeIs(200);
        $I->see('Sidebar left', '#sidebarLeft .block-title');
        $I->see('Block in sidebar left region', '#sidebarLeft .block-body');
        $I->dontSee('Sidebar right', '#sidebarRight .block-title');
        $I->dontSee('Block in sidebar right region', '#sidebarRight .block-body');
    }

    public function blockShouldRemainHiddenWithFilterSetToShowAndHideForSpecificContent(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);

        $content = dispatch_now(CreateContent::content('Content', $en, $user, [
            'published_at' => Carbon::now(),
            'is_active'    => true
        ]));

        dispatch_now(CreateBlock::basic('Sidebar right', $en, $user, [
            'body'      => 'Block in sidebar right region',
            'region'    => 'sidebarRight',
            'filter'    => ['+' => [$content->id . '/'], '-' => [$content->id . '/']],
            'is_active' => true
        ]));

        $I->amOnPage('content');
        $I->seeResponseCodeIs(200);
        $I->dontSee('Sidebar right', '#sidebarRight .block-title');
        $I->dontSee('Block in sidebar right region', '#sidebarRight .block-body');
    }


    public function shouldGetOnlyActiveFilesSyncedWithBlock(FunctionalTester $I)
    {
        $author   = factory(User::class)->create();
        $language   = new Language(['code' => 'en']);
        $block = dispatch_now(CreateBlock::basic('Block title', $language, $author, [
            'body'      => 'Block body',
            'region'    => 'homepage',
            'is_active' => true
        ]));

        Storage::fake('uploads');
        $activeImage = UploadedFile::fake()->image('active-file.jpg')->size(10);
        $inactiveImage = UploadedFile::fake()->image('inactive-file.jpg')->size(10);

        $activeFile = dispatch_now(CreateFile::image($activeImage, 'Image', $language, $author, [
            'info'        => 'active\'s file info',
            'description' => 'active\'s file description',
            'is_active'   => true,
        ]));
        $inactiveFile = dispatch_now(CreateFile::image($inactiveImage, 'Image', $language, $author, [
            'info'        => 'inactive\'s file info',
            'description' => 'inactive\'s file description',
            'is_active'   => false,
        ]));

        dispatch_now(new SyncFiles($block, [
            $activeFile->id => ['weight' => 3],
            $inactiveFile->id => ['weight' => 2]
        ]));

        $I->assertCount(1, $block->fresh()->files()->get());
        $I->assertEquals('active\'s file info', $block->fresh()->files()->first()->info);
    }
}
