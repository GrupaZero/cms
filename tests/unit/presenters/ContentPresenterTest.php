<?php namespace Cms;

use Carbon\Carbon;
use Codeception\Test\Unit;
use Gzero\Cms\Presenters\ContentPresenter;
use Gzero\Core\Models\User;

class ContentPresenterTest extends Unit {

    /** @test */
    public function canInstantiate()
    {
        $this->assertInstanceOf(ContentPresenter::class, new ContentPresenter([]));
    }

    /** @test */
    public function canAccessArrayValuesAsObjectProperties()
    {
        $date      = Carbon::now();
        $user      = factory(User::class)->create(['name' => 'John Doe']);
        $presenter = new ContentPresenter([
            'id'                 => 1,
            'theme'              => 'is-sticky',
            'weight'             => 10,
            'is_active'          => true,
            'is_on_home'         => true,
            'is_promoted'        => true,
            'is_sticky'          => true,
            'is_comment_allowed' => true,
            'published_at'       => $date,
            'author'             => $user,
            'routes'             => [
                [
                    'language_code' => 'en',
                    'path'          => 'example-title',
                    'is_active'     => true,
                ]
            ],
            'translations'       => [
                [
                    'language_code'   => 'en',
                    'title'           => 'Example title',
                    'teaser'          => 'Example teaser',
                    'body'            => 'Example body',
                    'seo_title'       => 'SEO title',
                    'seo_description' => 'SEO description',
                ]
            ]
        ]);

        $this->assertTrue($presenter->isOnHome());
        $this->assertTrue($presenter->isPromoted());
        $this->assertTrue($presenter->isSticky());
        $this->assertTrue($presenter->isCommentAllowed());
        $this->assertEquals(1, $presenter->getId());
        $this->assertEquals('Example title', $presenter->getTitle());
        $this->assertEquals('Example teaser', $presenter->getTeaser());
        $this->assertEquals('Example body', $presenter->getBody());
        $this->assertEquals(urlMl('example-title', 'en'), $presenter->getUrl());
        $this->assertEquals('is-sticky', $presenter->getTheme());
        $this->assertEquals('SEO title', $presenter->getSeoTitle());
        $this->assertEquals('SEO description', $presenter->getSeoDescription());
        $this->assertEquals($date, $presenter->getPublishDate());
        $this->assertEquals('John Doe', $presenter->getAuthor()->displayName());
    }

    /** @test */
    public function shouldBeAbleToGetTitleInSpecifiedLanguage()
    {
        $presenter = new ContentPresenter([
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                ],
                [
                    'language_code' => 'pl',
                    'title'         => 'Przykładowy tytuł',
                ]
            ]
        ]);

        $this->assertEquals('Example title', $presenter->getTitle());
        $this->assertEquals('Przykładowy tytuł', $presenter->getTitle('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetBodyInSpecifiedLanguage()
    {
        $presenter = new ContentPresenter([
            'translations' => [
                [
                    'language_code' => 'en',
                    'body'          => 'Example body',
                ],
                [
                    'language_code' => 'pl',
                    'body'          => 'Przykładowa treść',
                ]
            ]
        ]);

        $this->assertEquals('Example body', $presenter->getBody());
        $this->assertEquals('Przykładowa treść', $presenter->getBody('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetTeaserInSpecifiedLanguage()
    {
        $presenter = new ContentPresenter([
            'translations' => [
                [
                    'language_code' => 'en',
                    'teaser'        => 'Example teaser',
                ],
                [
                    'language_code' => 'pl',
                    'teaser'        => 'Przykładowy wstęp',
                ]
            ]
        ]);

        $this->assertEquals('Example teaser', $presenter->getTeaser());
        $this->assertEquals('Przykładowy wstęp', $presenter->getTeaser('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetUrlInSpecifiedLanguage()
    {
        $presenter = new ContentPresenter([
            'routes' => [
                [
                    'language_code' => 'en',
                    'path'          => 'example-title',
                    'is_active'     => true,
                ],
                [
                    'language_code' => 'pl',
                    'path'          => 'przykladowy-tytul',
                    'is_active'     => true,
                ]
            ]
        ]);

        $this->assertEquals(urlMl('example-title', 'en'), $presenter->getUrl());
        $this->assertEquals(urlMl('przykladowy-tytul', 'pl'), $presenter->getUrl('pl'));
    }

    /** @test */
    public function shouldNotBeAbleToGetInactiveUrlInSpecifiedLanguage()
    {
        $presenter = new ContentPresenter([
            'routes' => [
                [
                    'language_code' => 'en',
                    'path'          => 'example-title',
                    'is_active'     => false,
                ],
                [
                    'language_code' => 'pl',
                    'path'          => 'przykladowy-tytul',
                    'is_active'     => false,
                ]
            ]
        ]);

        $this->assertNull($presenter->getUrl());
        $this->assertNull($presenter->getUrl('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetSeoTitleFromAlternativeField()
    {
        $presenter = new ContentPresenter([
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                    'seo_title'     => ''
                ]
            ]
        ]);

        $this->assertEquals('Example title', $presenter->getSeoTitle());
    }

    /** @test */
    public function shouldBeAbleToGetSeoDescriptionFromAlternativeField()
    {
        $presenter = new ContentPresenter([
            'translations' => [
                [
                    'language_code'   => 'en',
                    'body'            => 'Example body',
                    'seo_description' => '',
                ]
            ]
        ]);

        $this->assertEquals('Example body', $presenter->getSeoDescription());
    }
}
