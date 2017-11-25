<?php namespace Cms;

use Carbon\Carbon;
use Codeception\Test\Unit;
use Gzero\Cms\Handlers\Content\ContentHandler;
use Gzero\Cms\Models\Content;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\DeleteContent;
use Gzero\Cms\Jobs\DeleteContentTranslation;
use Gzero\Cms\Jobs\ForceDeleteContent;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Models\ContentTranslation;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\Route;
use Gzero\Core\Exception;

class ContentJobsTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @test */
    public function canCreateContent()
    {
        $user    = $this->tester->haveUser();
        $content = dispatch_now(CreateContent::content('New One', new Language(['code' => 'en']), $user, [
            'weight'             => 10,
            'is_active'          => true,
            'is_on_home'         => true,
            'is_promoted'        => true,
            'is_sticky'          => true,
            'is_comment_allowed' => true
        ]));

        $content          = $content->fresh();
        $translation      = $content->translations->first();
        $routeTranslation = $content->routes->first();

        $this->assertTrue($content->is_on_home);
        $this->assertTrue($content->is_promoted);
        $this->assertTrue($content->is_sticky);
        $this->assertTrue($content->is_comment_allowed);
        $this->assertEquals(10, $content->weight);
        $this->assertEquals($user->id, $content->author->id, 'Author was set');

        $this->assertEquals('content', $content->type->name, 'Correct content type was set');
        $this->assertEquals(ContentHandler::class, $content->type->handler, 'Content type have correct handler');

        $this->assertInstanceOf(Carbon::class, $content->published_at);
        $this->assertTrue(Carbon::now()->addMinute()->greaterThan($content->published_at));

        $this->assertEquals('New One', $translation->title, 'Title was set');
        $this->assertEquals('en', $translation->language_code, 'Language code was set');
        $this->assertEquals($user->id, $translation->author->id, 'Translation author was set');

        $this->assertEquals('new-one', $routeTranslation->path, 'Language code was set');
        $this->assertEquals('en', $routeTranslation->language_code, 'Route language code was set');
        $this->assertTrue($routeTranslation->is_active, 'Route was set to active');
    }

    /** @test */
    public function itCreateUnpublishedContentByDefault()
    {
        $user    = $this->tester->haveUser();
        $content = dispatch_now(CreateContent::content('New One', new Language(['code' => 'en']), $user));

        $routeTranslation = $content->routes->first();

        $this->assertFalse($routeTranslation->is_active, 'Route was set to active');
    }

    /** @test */
    public function canCreateContentAsAChild()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);
        $parent   = $this->tester->haveContent([
            'type'         => 'category',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Parent Title'
                ]
            ]
        ]);

        $child = dispatch_now(CreateContent::category('Child Title', $language, $user, [
            'parent_id' => $parent->id,
            'is_active' => true
        ]));

        $child      = $child->fresh();
        $childRoute = $child->routes->first();

        $nestedChild = dispatch_now(CreateContent::content('Nested Child Title', $language, $user, [
            'parent_id' => $child->id,
            'is_active' => true
        ]));

        $nestedChild      = $nestedChild->fresh();
        $nestedChildRoute = $nestedChild->routes->first();

        $this->assertEquals($child->parent_id, $parent->id);
        $this->assertEquals('parent-title/child-title', $childRoute->path);
        $this->assertEquals($nestedChild->parent_id, $child->id);
        $this->assertEquals('parent-title/child-title/nested-child-title', $nestedChildRoute->path);
    }

    /** @test */
    public function cantCreateContentAsAChildOfNotExistingParent()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);

        try {
            dispatch_now(CreateContent::category('Child Title', $language, $user, [
                'parent_id' => 100,
                'is_active' => true
            ]));
        } catch (Exception $exception) {
            $this->assertEquals(Exception::class, get_class($exception));
            $this->assertEquals('Parent not found', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');

    }

    /** @test */
    public function itAllowsOnlyCategoryToBeSetAsParent()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);
        $parent   = $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Parent Title'
                ]
            ]
        ]);

        try {
            dispatch_now(CreateContent::content('title', $language, $user, ['parent_id' => $parent->id]));
        } catch (Exception $exception) {
            $this->assertEquals(Exception::class, get_class($exception));
            $this->assertEquals('Content can be assigned only to category parent', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function itValidatesContentType()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);

        try {
            dispatch_now(CreateContent::make('title', $language, $user, ['type' => 'post']));
        } catch (Exception $exception) {
            $this->assertEquals(Exception::class, get_class($exception));
            $this->assertEquals('Unknown content type', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function canAddContentTranslation()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);
        $content  = $this->tester->haveContent();

        $this->assertEquals(0, $content->translations()->count());

        $translation = dispatch_now(new AddContentTranslation($content, 'New example', $language, $user,
            [
                'teaser'          => 'Teaser',
                'body'            => 'Body',
                'seo_title'       => 'SEO title',
                'seo_description' => 'SEO description',
            ]
        ));

        $translation = $translation->fresh();

        $this->assertEquals(1, $content->translations()->count());
        $this->assertEquals('New example', $translation->title);
        $this->assertEquals('Teaser', $translation->teaser);
        $this->assertEquals('Body', $translation->body);
        $this->assertEquals('SEO title', $translation->seo_title);
        $this->assertEquals('SEO description', $translation->seo_description);
        $this->assertEquals($language->code, $translation->language_code);
        $this->assertEquals($user->id, $translation->author->id);
        $this->assertTrue($translation->is_active);
    }

    /** @test */
    public function onlyRecentlyAddedTranslationShouldBeMarkedAsActive()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);
        $content  = $this->tester->haveContent(
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title'
                    ]
                ]
            ]
        );

        $oldTranslation = $content->translations->first();

        $this->assertNotNull($oldTranslation);

        dispatch_now(new AddContentTranslation($content, 'New example', $language, $user));

        $newTranslation = $content->fresh()->translations->first();
        $oldTranslation = $oldTranslation->fresh();

        $this->assertEquals(1, $content->translations()->count());
        $this->assertEquals('en', $newTranslation->language_code);
        $this->assertEquals(true, $newTranslation->is_active);

        $this->assertEquals(2, $content->translations(false)->count());
        $this->assertEquals('en', $oldTranslation->language_code);
        $this->assertEquals(false, $oldTranslation->is_active);
    }

    /** @test */
    public function shouldCreateContentRouteWithUniquePathFromTranslationTitle()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'en']);
        $category = $this->tester->haveContent([
            'type'         => 'category',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Category Title'
                ]
            ]
        ]);
        $content  = $this->tester->haveContent([
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Content Title'
                ]
            ]
        ]);
        $content->setChildOf($category);

        dispatch_now(new AddContentTranslation($category, 'Category Title', $language, $user));
        dispatch_now(new AddContentTranslation($content, 'Content Title', $language, $user));

        $content       = $content->fresh();
        $category      = $category->fresh();
        $contentRoute  = $content->routes;
        $categoryRoute = $category->routes;

        // Category has only one translation because it was found and updated
        $this->assertEquals('en', $categoryRoute->first()->language_code);
        $this->assertEquals('category-title-1', $categoryRoute->first()->path);
        $this->assertTrue($categoryRoute->first()->is_active);

        // Content has two translations because translation was added after content was assigned as child
        // so the `category-title-1/content-title` was searched and `content-title` is in database
        // because setChildOf is not mean to update route path
        $this->assertEquals('en', $contentRoute->first()->language_code);
        $this->assertEquals('content-title', $contentRoute->first()->path);
        $this->assertEquals('en', $contentRoute->last()->language_code);
        $this->assertEquals('category-title-1/content-title', $contentRoute->last()->path);
    }

    /** @test */
    public function shouldCreateContentRouteWhenAddingTranslationInNewLanguage()
    {
        $user     = $this->tester->haveUser();
        $language = new Language(['code' => 'pl']);
        $content  = $this->tester->haveContent([
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example Title'
                ]
            ]
        ]);

        dispatch_now(new AddContentTranslation($content, 'Example Title', $language, $user));

        $content = Content::find($content->id);
        $route   = $content->routes->last();

        $this->assertEquals('example-title', $route->path);
        $this->assertEquals('pl', $route->language_code);
        $this->assertTrue($route->is_active);
    }

    /** @test */
    public function canDeleteContent()
    {
        $content = $this->tester->haveContent();

        dispatch_now(new DeleteContent($content));

        $this->assertNull(Content::find($content->id));
    }

    /** @test */
    public function canDeleteContentWithChildren()
    {
        $category = $this->tester->haveContent(['type' => 'category']);
        $content  = $this->tester->haveContent();

        $content->setChildOf($category);

        dispatch_now(new DeleteContent($category));

        $this->assertEmpty($category->fresh()->children()->get());
    }

    /** @test */
    public function canForceDeleteContent()
    {
        $contents = $this->tester->haveContents([
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title'
                    ]
                ]
            ],
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Other title'
                    ]
                ]
            ]
        ]);

        $content            = Content::find($contents[0]->id);
        $notRelatedContent  = Content::find($contents[1]->id);
        $contentTranslation = $content->translations->first();
        $contentRoute       = $content->routes->first();

        dispatch_now(new ForceDeleteContent($content));

        $this->assertNull(Content::find($content->id));
        $this->assertNull(ContentTranslation::find($contentTranslation->id));
        $this->assertNull(Route::find($contentRoute->id));
        $this->assertNotNull(Content::find($notRelatedContent->id));
    }

    /** @test */
    public function canForceDeleteContentWithChildren()
    {
        $category = $this->tester->haveContent(['type' => 'category']);
        $content  = $this->tester->haveContent();
        $content->setChildOf($category);

        dispatch_now(new ForceDeleteContent($category));

        $this->assertNull(Content::find($category->id));
        $this->assertNull(Content::find($content->id));
    }

    /** @test */
    public function canForceDeleteSoftDeletedContent()
    {
        $contents = $this->tester->haveContents([
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title'
                    ]
                ]
            ],
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Other title'
                    ]
                ]
            ]
        ]);

        $content            = Content::find($contents[0]->id);
        $notRelatedContent  = Content::find($contents[1]->id);
        $contentTranslation = $content->translations->first();
        $contentRoute       = $content->routes->first();

        dispatch_now(new DeleteContent($content));

        $this->assertNull(Content::find($content->id));
        $this->assertNotNull(Content::withTrashed()->find($content->id));
        $this->assertNotNull(Content::find($notRelatedContent->id));

        dispatch_now(new ForceDeleteContent($content));

        $this->assertNull(Content::withTrashed()->find($content->id));
        $this->assertNull(ContentTranslation::find($contentTranslation->id));
        $this->assertNull(Route::find($contentRoute->id));
        $this->assertNotNull(Content::find($notRelatedContent->id));
    }

    /** @test */
    public function canForceDeleteSoftDeletedContentWithChildren()
    {
        $category = $this->tester->haveContent(['type' => 'category']);
        $content  = $this->tester->haveContent();
        $content->setChildOf($category);

        dispatch_now(new DeleteContent($category));

        $this->assertNull(Content::find($category->id));
        $this->assertNull(Content::find($content->id));
        $this->assertNotNull(Content::withTrashed()->find($category->id));
        $this->assertNotNull(Content::withTrashed()->find($content->id));

        dispatch_now(new ForceDeleteContent($category));

        $this->assertNull(Content::withTrashed()->find($category->id));
        $this->assertNull(Content::withTrashed()->find($content->id));
    }

    /** @test */
    public function canDeleteContentInactiveTranslation()
    {
        $withActive = false;
        $content    = $this->tester->haveContent(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title',
                        'is_active'     => false
                    ],
                    [
                        'language_code' => 'en',
                        'title'         => 'Updated title title',
                        'is_active'     => true
                    ]
                ]
            ]
        );

        $this->assertEquals(2, $content->translations($withActive)->count());

        dispatch_now(new DeleteContentTranslation($content->translations($withActive)->first()));

        $this->assertEquals(1, $content->translations($withActive)->count());
    }

    /** @test */
    public function itShouldForceDeleteOneContent()
    {
        $contents = $this->tester->haveContents([
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title'
                    ]
                ]
            ],
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Other title'
                    ]
                ]
            ]
        ]);

        $first  = head($contents);
        $second = last($contents);

        dispatch_now(new DeleteContent($first));
        dispatch_now(new DeleteContent($second));

        $this->assertNull(Content::find($first->id));
        $this->assertNull(Content::find($second->id));

        dispatch_now(new ForceDeleteContent($first));

        $this->assertNull(Content::find($first->id));
        $this->assertNotNull(Content::withTrashed()->find($second->id));
    }

    /** @test */
    public function itDoesNotAllowToDeleteActiveTranslation()
    {
        $content = $this->tester->haveContent(
            [
                'type'         => 'content',
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title'
                    ]
                ]
            ]
        );

        try {
            dispatch_now(new DeleteContentTranslation($content->translations->first()));
        } catch (Exception $exception) {
            $this->assertEquals(Exception::class, get_class($exception));
            $this->assertEquals('Cannot delete active translation', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }
}
