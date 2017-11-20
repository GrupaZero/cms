<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\CreateContentRoute;
use Gzero\Cms\Jobs\AddContentTranslation;
use Gzero\Cms\Repositories\ContentReadRepository;
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

}

