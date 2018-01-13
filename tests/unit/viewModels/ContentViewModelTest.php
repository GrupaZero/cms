<?php namespace Cms;

use Carbon\Carbon;
use Codeception\Test\Unit;
use Gzero\Cms\ViewModels\ContentViewModel;
use Gzero\Core\Models\User;

class ContentViewModelTest extends Unit {

    /** @test */
    public function canInstantiate()
    {
        $this->assertInstanceOf(ContentViewModel::class, new ContentViewModel([]));
    }

    /** @test */
    public function canAccessArrayValuesAsObjectProperties()
    {
        $date      = Carbon::now();
        $user      = factory(User::class)->create(['name' => 'John Doe']);
        $presenter = new ContentViewModel([
            'id'                 => 1,
            'theme'              => 'is-sticky',
            'weight'             => 10,
            'is_active'          => true,
            'is_on_home'         => true,
            'is_promoted'        => true,
            'is_sticky'          => true,
            'is_comment_allowed' => true,
            'published_at'       => $date,
            'author'             => $user->toArray(),
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
        $this->assertEquals(1, $presenter->id());
        $this->assertEquals('Example title', $presenter->title());
        $this->assertEquals('Example teaser', $presenter->teaser());
        $this->assertEquals('Example body', $presenter->body());
        $this->assertEquals(urlMl('example-title', 'en'), $presenter->url());
        $this->assertEquals('is-sticky', $presenter->theme());
        $this->assertEquals('SEO title', $presenter->seoTitle());
        $this->assertEquals('SEO description', $presenter->seoDescription());
        $this->assertEquals($date, $presenter->publishedAt());
        $this->assertEquals('John Doe', $presenter->author()->displayName());
    }

    /** @test */
    public function shouldBeAbleToGetTitleInSpecifiedLanguage()
    {
        $presenter = new ContentViewModel([
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

        $this->assertEquals('Example title', $presenter->title());
        $this->assertEquals('Przykładowy tytuł', $presenter->title('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetBodyInSpecifiedLanguage()
    {
        $presenter = new ContentViewModel([
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

        $this->assertEquals('Example body', $presenter->body());
        $this->assertEquals('Przykładowa treść', $presenter->body('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetTeaserInSpecifiedLanguage()
    {
        $presenter = new ContentViewModel([
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

        $this->assertEquals('Example teaser', $presenter->teaser());
        $this->assertEquals('Przykładowy wstęp', $presenter->teaser('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetUrlInSpecifiedLanguage()
    {
        $presenter = new ContentViewModel([
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

        $this->assertEquals(urlMl('example-title', 'en'), $presenter->url());
        $this->assertEquals(urlMl('przykladowy-tytul', 'pl'), $presenter->url('pl'));
    }

    /** @test */
    public function shouldNotBeAbleToGetInactiveUrlInSpecifiedLanguage()
    {
        $presenter = new ContentViewModel([
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

        $this->assertNull($presenter->url());
        $this->assertNull($presenter->url('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetSeoTitleFromAlternativeField()
    {
        $presenter = new ContentViewModel([
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                    'seo_title'     => ''
                ]
            ]
        ]);

        $this->assertEquals('Example title', $presenter->seoTitle());
    }

    /** @test */
    public function shouldBeAbleToGetSeoDescriptionFromAlternativeField()
    {
        $presenter = new ContentViewModel([
            'translations' => [
                [
                    'language_code'   => 'en',
                    'body'            => 'Example body',
                    'seo_description' => '',
                ]
            ]
        ]);

        $this->assertEquals('Example body', $presenter->seoDescription());
    }

    /** @test */
    public function shouldBeAbleToGetFirstImageUrlFromSpecifiedField()
    {
        $presenter = new ContentViewModel([
            'translations' => [
                [
                    'language_code' => 'en',
                    'body'          => 'Example body <img src="http://localhost/images/first-image.png" class="img-fluid">'
                ]
            ]
        ]);

        $this->assertEquals('http://localhost/images/first-image.png', $presenter->firstImageUrl($presenter->body()));
    }

    /** @test */
    public function shouldBeAbleToGetAncestorsNamesFromRoutePath()
    {
        $presenter = new ContentViewModel([
            'routes' => [
                [
                    'language_code' => 'en',
                    'path'          => 'offer/category/example-title',
                    'is_active'     => true,
                ]
            ]
        ]);

        $this->assertEquals(['Offer', 'Category'], $presenter->ancestorsNames());
    }
}
