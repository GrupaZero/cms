<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\DeleteContent;
use Gzero\Cms\Jobs\DeleteContentTranslation;
use Gzero\Cms\Jobs\ForceDeleteContent;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Repositories\RouteReadRepository;
use Illuminate\Support\Facades\Auth;
use Gzero\Core\Exception;

class ContentJobsTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var ContentReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new ContentReadRepository();
    }

    /** @test */
    public function canCreateContentAndGetItById()
    {

        $content       = (new CreateContent('content', 'en', 'New One',
            [
                'weight'             => 0,
                'is_active'          => true,
                'is_on_home'         => true,
                'is_promoted'        => true,
                'is_sticky'          => true,
                'is_comment_allowed' => true
            ]
        ))->handle();
        $contentFromDb = $this->repository->getById($content->id);
        $translation   = $contentFromDb->translations->first();
        $route         = $contentFromDb->route->translations->first();

        $this->assertEquals(
            [
                $content->id,
                $content->type,
                $content->is_active,
                $content->is_on_home,
                $content->is_promoted,
                $content->is_sticky,
                $content->is_comment_allowed,
                $content->published_at,
                'en',
                'New One',
                'new-one',
                true
            ],
            [
                $contentFromDb->id,
                $contentFromDb->type,
                $contentFromDb->is_active,
                $contentFromDb->is_on_home,
                $contentFromDb->is_promoted,
                $contentFromDb->is_sticky,
                $contentFromDb->is_comment_allowed,
                $contentFromDb->published_at,
                $translation->language_code,
                $translation->title,
                $route->path,
                $route->is_active
            ]
        );
    }

    /** @test */
    public function canCreateContentAsChild()
    {
        $parent = $this->tester->haveContent([
            'type'         => 'category',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Parent Title'
                ]
            ]
        ]);

        $child       = (new CreateContent('category', 'en', 'Child Title', ['parent_id' => $parent->id]))->handle();
        $childFromDb = $this->repository->getById($child->id);
        $childRoute  = $childFromDb->route->translations->first();

        $nestedChild       = (new CreateContent('category', 'en', 'Nested Child Title', ['parent_id' => $child->id]))->handle();
        $nestedChildFromDb = $this->repository->getById($nestedChild->id);
        $nestedChildRoute  = $nestedChildFromDb->route->translations->first();

        $this->assertEquals($child->parent_id, $childFromDb->parent_id);
        $this->assertEquals('parent-title/child-title', $childRoute->path);
        $this->assertEquals($nestedChild->parent_id, $nestedChildFromDb->parent_id);
        $this->assertEquals('parent-title/child-title/nested-child-title', $nestedChildRoute->path);
    }

    /** @test */
    public function itAllowsOnlyCategoryToBeSetAsParent()
    {
        $parent = $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Parent Title'
                ]
            ]
        ]);

        try {
            (new CreateContent('content', 'en', 'title', ['parent_id' => $parent->id]))->handle();
        } catch (Exception $exception) {
            $this->assertEquals(Exception::class, get_class($exception));
            $this->assertEquals("Content type 'content' is not allowed for the parent type.", $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function itValidatesContentType()
    {
        try {
            (new CreateContent('post', 'en', 'title'))->handle();
        } catch (Exception $exception) {
            $this->assertEquals(Exception::class, get_class($exception));
            $this->assertEquals("The 'post' is an invalid content type.", $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function canCreateContentWithAuthor()
    {
        $this->tester->loginAsUser();
        $author = $this->tester->haveUser();

        $content       = (new CreateContent('content', 'en', 'title', [], $author))->handle();
        $contentFromDb = $this->repository->getById($content->id);

        $this->assertEquals($author->id, $contentFromDb->author_id);
        $this->assertEquals($author->email, $contentFromDb->author['email']);
    }

    /** @test */
    public function canCreateContentWithoutAuthor()
    {

        $content       = (new CreateContent('content', 'en', 'title'))->handle();
        $contentFromDb = $this->repository->getById($content->id);

        $this->assertNull($contentFromDb->author_id);
        $this->assertNull($contentFromDb->author);
    }

    /** @test */
    public function cantCreateContentWithoutAuthorWhenUserIsLoggedIn()
    {
        $this->tester->loginAsUser();
        $author = Auth::user();

        $content       = (new CreateContent('content', 'en', 'title'))->handle();
        $contentFromDb = $this->repository->getById($content->id);

        $this->assertEquals($author->id, $contentFromDb->author_id);
        $this->assertEquals($author->email, $contentFromDb->author['email']);
    }

    /** @test */
    public function canAddContentTranslationAndGetItById()
    {
        $content = $this->tester->haveContent();

        $translation       = (new AddContentTranslation($content, 'en', 'New example',
            [
                'teaser'          => 'Teaser',
                'body'            => 'Body',
                'seo_title'       => 'SEO title',
                'seo_description' => 'SEO description'
            ]
        ))->handle();
        $translationFromDb = $this->repository->getTranslationById($translation->id);

        $this->assertEquals(
            [
                $translation->id,
                $translation->language_code,
                $translation->title,
                $translation->body,
                $translation->seo_title,
                $translation->seo_description,
                $translation->is_active
            ],
            [
                $translationFromDb->id,
                $translationFromDb->language_code,
                $translationFromDb->title,
                $translationFromDb->body,
                $translationFromDb->seo_title,
                $translationFromDb->seo_description,
                $translationFromDb->is_active
            ]
        );
    }

    /** @test */
    public function onlyRecentlyAddedTranslationShouldBeMarkedAsActive()
    {
        $content = $this->tester->haveContent(
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'Example title'
                    ]
                ]
            ]
        );

        $oldTranslation       = $content->translations->first();
        $translation          = (new AddContentTranslation($content, 'en', 'New example'))->handle();
        $translationFromDb    = $this->repository->getTranslationById($translation->id);
        $oldTranslationFromDb = $this->repository->getTranslationById($oldTranslation->id);

        $this->assertEquals('en', $translationFromDb->language_code);
        $this->assertEquals(true, $translationFromDb->is_active);
        $this->assertEquals('en', $oldTranslationFromDb->language_code);
        $this->assertEquals(false, $oldTranslationFromDb->is_active);
    }

    /** @test */
    public function canCreateContentRouteFromTranslationTitleAndGetItByPath()
    {
        $content = $this->tester->haveContent();

        (new AddContentTranslation($content, 'en', 'Example Title'))->handle();
        $contentFromDb = $this->repository->getByPath('example-title', 'en');
        $translations  = $contentFromDb->route->translations->first();

        $this->assertEquals($content->id, $contentFromDb->id);
        $this->assertEquals('example-title', $translations->path);
        $this->assertEquals('en', $translations->language_code);
    }

    /** @test */
    public function shouldCreateContentRouteWithUniquePathFromTranslationTitle()
    {
        $content = $this->tester->haveContent([
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example Title'
                ]
            ]
        ]);

        (new AddContentTranslation($content, 'en', 'Example Title'))->handle();
        $contentFromDb          = $this->repository->getByPath('example-title-1', 'en');
        $translations           = $contentFromDb->route->translations->first();
        $contentByOldPathFromDb = $this->repository->getByPath('example-title', 'en');

        $this->assertNull($contentByOldPathFromDb);
        $this->assertEquals($content->id, $contentFromDb->id);
        $this->assertEquals('example-title-1', $translations->path);
        $this->assertEquals('en', $translations->language_code);
    }

    /** @test */
    public function canDeleteContent()
    {
        $content = $this->tester->haveContent();

        (new DeleteContent($content))->handle();

        $this->assertNull($this->repository->getById($content->id));
    }

    /** @test */
    public function canDeleteContentWithChildren()
    {
        $category = (new CreateContent('category', 'en', 'category title'))->handle();

        $content = (new CreateContent('content', 'en', 'content title'))->handle();
        $content->setChildOf($category);

        (new DeleteContent($category))->handle();

        // Content children has been removed?
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

        $newContent         = $this->repository->getById($contents[0]->id);
        $notRelatedContent  = $this->repository->getById($contents[1]->id);
        $contentTranslation = $newContent->translations()->first();
        $contentRoute       = $newContent->route()->first();

        (new ForceDeleteContent($newContent))->handle();

        // Get not related content
        $content2 = $this->repository->getById($notRelatedContent->id);

        // Check if content has been removed
        $this->assertNull($this->repository->getById($newContent->id));
        // Check if content translations has been removed
        $this->assertNull($this->repository->getTranslationById($contentTranslation->id));
        // Check if content route has been removed
        $this->assertNull((new RouteReadRepository())->getById($contentRoute->id));
        // Check if not related content has not be removed
        $this->assertNotNull($content2);
        // Check if content route translation been removed
        $this->assertNull($this->repository->getByPath(
            $contentRoute->translations->first()->path,
            $contentRoute->translations->first()->language_code
        ));
    }

    /** @test */
    public function canForceDeleteContentWithChildren()
    {
        $category = (new CreateContent('category', 'en', 'category title'))->handle();

        $content = (new CreateContent('content', 'en', 'content title'))->handle();
        $content->setChildOf($category);

        (new ForceDeleteContent($category))->handle();

        $this->assertNull($this->repository->getById($category->id));
        // Content children has been removed?
        $this->assertNull($this->repository->getById($content->id));
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

        $newContent         = $this->repository->getById($contents[0]->id);
        $notRelatedContent  = $this->repository->getById($contents[1]->id);
        $contentTranslation = $newContent->translations()->first();
        $contentRoute       = $newContent->route()->first();

        (new DeleteContent($newContent))->handle();
        (new ForceDeleteContent($newContent))->handle();

        // Get not related content
        $content2 = $this->repository->getById($notRelatedContent->id);
        // Check if content has been removed
        $this->assertNull($this->repository->getById($newContent->id));
        // Check if content translations has been removed
        $this->assertNull($this->repository->getTranslationById($contentTranslation->id));
        // Check if content route has been removed
        $this->assertNull((new RouteReadRepository())->getById($contentRoute->id));
        // Check if not related content has not be removed
        $this->assertNotNull($content2);
        // Check if content route translation been removed
        $this->assertNull($this->repository->getByPath(
            $contentRoute->translations->first()->path,
            $contentRoute->translations->first()->language_code
        ));
    }

    /**
     * @test
     */
    public function canForceDeleteSoftDeletedContentWithChildren()
    {
        $category = (new CreateContent('category', 'en', 'category title'))->handle();

        $content = (new CreateContent('content', 'en', 'content title'))->handle();
        $content->setChildOf($category);

        (new DeleteContent($category))->handle();
        (new ForceDeleteContent($category))->handle();

        $this->assertNull($this->repository->getById($category->id));
        // Content children has been removed?
        $this->assertNull($this->repository->getById($content->id));
    }

    /** @test */
    public function canDeleteContentTranslation()
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

        (new DeleteContentTranslation($content->translations($withActive)->first()))->handle();

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

        $first = head($contents);
        $second = last($contents);

        (new DeleteContent($first))->handle();
        (new DeleteContent($second))->handle();

        $this->assertNull($this->repository->getById($first->id));
        $this->assertNull($this->repository->getById($second->id));

        (new ForceDeleteContent($first))->handle();

        $this->assertNull($this->repository->getDeletedById($first->id));
        $this->assertNotNull($this->repository->getDeletedById($second->id));
    }

    /** @test */
    public function itShouldRetrieveNonTrashedContent()
    {
        $content = $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title'
                ]
            ]
        ]);

        $result = $this->repository->getByIdWithTrashed($content->id);
        $this->assertEquals($content->id, $result->id);
    }

    /** @test */
    public function itShouldRetrieveTrashedContent()
    {
        $content = $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title'
                ]
            ]
        ]);

        (new DeleteContent($content))->handle();

        $result = $this->repository->getByIdWithTrashed($content->id);

        $this->assertEquals($content->id, $result->id);
    }

    /** @test */
    public function itShouldNotRetrieveForceDeletedContent()
    {
        $content = $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title'
                ]
            ]
        ]);

        (new ForceDeleteContent($content))->handle();

        $this->assertNull($this->repository->getByIdWithTrashed($content->id));
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

        $this->assertInstanceOf('Gzero\Cms\Models\Content', $content);

        $translation = $this->repository->getById($content->id)->translations->first();
        $this->assertInstanceOf('Gzero\Cms\Models\ContentTranslation', $translation);
        $this->assertEquals($translation->is_active, 1);

        $this->expectException('Gzero\Core\Exception');
        (new DeleteContentTranslation($translation))->handle();
    }
}

