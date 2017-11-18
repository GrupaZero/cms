<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Jobs\CreateContent;
use Gzero\Cms\Jobs\CreateContentRoute;
use Gzero\Cms\Jobs\CreateContentTranslation;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Exception;
use Illuminate\Support\Facades\Auth;

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
        $attributes = [
            'type'               => 'content',
            'is_on_home'         => true,
            'is_comment_allowed' => true,
            'is_promoted'        => true,
            'is_sticky'          => true,
            'is_active'          => true,
            'published_at'       => date('Y-m-d H:i:s')
        ];

        $content       = (new CreateContent($attributes))->handle();
        $contentFromDb = $this->repository->getById($content->id);

        $this->assertEquals(
            [
                $content->id,
                $content->type,
                $content->is_active,
                $content->is_on_home,
                $content->is_promoted,
                $content->is_sticky,
                $content->is_comment_allowed,
                $content->published_at
            ],
            [
                $contentFromDb->id,
                $contentFromDb->type,
                $contentFromDb->is_active,
                $contentFromDb->is_on_home,
                $contentFromDb->is_promoted,
                $contentFromDb->is_sticky,
                $contentFromDb->is_comment_allowed,
                $contentFromDb->published_at
            ]
        );
    }

    /** @test */
    public function canCreateContentWithAuthor()
    {
        $this->tester->loginAsUser();
        $author = $this->tester->haveUser();

        $content       = (new CreateContent(['type' => 'content'], $author))->handle();
        $contentFromDb = $this->repository->getById($content->id);

        $this->assertEquals($author->id, $contentFromDb->author_id);
        $this->assertEquals($author->email, $contentFromDb->author['email']);
    }

    /** @test */
    public function canCreateContentWithoutAuthor()
    {

        $content       = (new CreateContent(['type' => 'content']))->handle();
        $contentFromDb = $this->repository->getById($content->id);

        $this->assertNull($contentFromDb->author_id);
        $this->assertNull($contentFromDb->author);
    }

    /** @test */
    public function cantCreateContentWithoutAuthorWhenUserIsLoggedIn()
    {
        $this->tester->loginAsUser();
        $author = Auth::user();

        $content       = (new CreateContent(['type' => 'content'], $author))->handle();
        $contentFromDb = $this->repository->getById($content->id);

        $this->assertEquals($author->id, $contentFromDb->author_id);
        $this->assertEquals($author->email, $contentFromDb->author['email']);
    }

    /** @test */
    public function canCreateContentTranslationAndGetItById()
    {
        $content    = $this->tester->haveContent();
        $attributes = [
            'language_code'   => 'en',
            'title'           => 'New example',
            'teaser'          => 'Example teaser',
            'body'            => 'Example body',
            'seo_title'       => 'Example seo_title',
            'seo_description' => 'Example seo_description'
        ];

        $translation       = (new CreateContentTranslation($content, $attributes))->handle();
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
    public function itChecksExistenceOfLanguageCodeAndTitleWhenCreatingATranslation()
    {
        $content = $this->tester->haveContent();

        try {
            (new CreateContentTranslation($content, []))->handle();
        } catch (Exception $exception) {
            $this->assertEquals(Exception::class, get_class($exception));
            $this->assertEquals('Language code and title of translation are required', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
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

        $attributes = [
            'language_code' => 'en',
            'title'         => 'New example'
        ];

        $oldTranslation       = $content->translations->first();
        $translation          = (new CreateContentTranslation($content, $attributes))->handle();
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

        $attributes = [
            'language_code' => 'en',
            'title'         => 'Example Title'
        ];

        (new CreateContentTranslation($content, $attributes))->handle();
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

        $attributes = [
            'language_code' => 'en',
            'title'         => 'Example Title'
        ];

        (new CreateContentTranslation($content, $attributes))->handle();
        $contentFromDb          = $this->repository->getByPath('example-title-1', 'en');
        $translations           = $contentFromDb->route->translations->first();
        $contentByOldPathFromDb = $this->repository->getByPath('example-title', 'en');

        $this->assertNull($contentByOldPathFromDb);
        $this->assertEquals($content->id, $contentFromDb->id);
        $this->assertEquals('example-title-1', $translations->path);
        $this->assertEquals('en', $translations->language_code);
    }

}

