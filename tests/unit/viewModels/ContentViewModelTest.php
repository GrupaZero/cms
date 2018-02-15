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
        $viewModel = new ContentViewModel([
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

        $this->assertTrue($viewModel->isOnHome());
        $this->assertTrue($viewModel->isPromoted());
        $this->assertTrue($viewModel->isSticky());
        $this->assertTrue($viewModel->isCommentAllowed());
        $this->assertEquals(1, $viewModel->id());
        $this->assertEquals('Example title', $viewModel->title());
        $this->assertEquals('Example teaser', $viewModel->teaser());
        $this->assertEquals('Example body', $viewModel->body());
        $this->assertEquals(urlMl('example-title', 'en'), $viewModel->url());
        $this->assertEquals('is-sticky', $viewModel->theme());
        $this->assertEquals('SEO title', $viewModel->seoTitle());
        $this->assertEquals('SEO description', $viewModel->seoDescription());
        $this->assertEquals($date, $viewModel->publishedAt());
        $this->assertEquals('John Doe', $viewModel->author()->displayName());
    }

    /** @test */
    public function shouldBeAbleToGetTitleInSpecifiedLanguage()
    {
        $viewModel = new ContentViewModel([
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

        $this->assertEquals('Example title', $viewModel->title());
        $this->assertEquals('Przykładowy tytuł', $viewModel->title('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetBodyInSpecifiedLanguage()
    {
        $viewModel = new ContentViewModel([
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

        $this->assertEquals('Example body', $viewModel->body());
        $this->assertEquals('Przykładowa treść', $viewModel->body('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetTeaserInSpecifiedLanguage()
    {
        $viewModel = new ContentViewModel([
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

        $this->assertEquals('Example teaser', $viewModel->teaser());
        $this->assertEquals('Przykładowy wstęp', $viewModel->teaser('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetUrlInSpecifiedLanguage()
    {
        $viewModel = new ContentViewModel([
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

        $this->assertEquals(urlMl('example-title', 'en'), $viewModel->url());
        $this->assertEquals(urlMl('przykladowy-tytul', 'pl'), $viewModel->url('pl'));
    }

    /** @test */
    public function shouldNotBeAbleToGetInactiveUrlInSpecifiedLanguage()
    {
        $viewModel = new ContentViewModel([
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

        $this->assertNull($viewModel->url());
        $this->assertNull($viewModel->url('pl'));
    }

    /** @test */
    public function shouldBeAbleToGetSeoTitleFromAlternativeField()
    {
        $viewModel = new ContentViewModel([
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                    'seo_title'     => ''
                ]
            ]
        ]);

        $this->assertEquals('Example title', $viewModel->seoTitle());
    }

    /** @test */
    public function shouldBeAbleToGetSeoDescriptionFromAlternativeField()
    {
        $viewModel = new ContentViewModel([
            'translations' => [
                [
                    'language_code'   => 'en',
                    'body'            => 'Example body',
                    'seo_description' => '',
                ]
            ]
        ]);

        $this->assertEquals('Example body', $viewModel->seoDescription());
    }

    /** @test */
    public function shouldBeAbleToGetFirstImageUrlFromSpecifiedField()
    {
        $viewModel = new ContentViewModel([
            'translations' => [
                [
                    'language_code' => 'en',
                    'body'          => 'Example body <img src="http://localhost/images/first-image.png" class="img-fluid">'
                ]
            ]
        ]);

        $this->assertEquals('http://localhost/images/first-image.png', $viewModel->firstImageUrl($viewModel->body()));
    }

    /** @test */
    public function shouldBeAbleToGetAncestorsNamesFromRoutePath()
    {
        $viewModel = new ContentViewModel([
            'routes' => [
                [
                    'language_code' => 'en',
                    'path'          => 'offer/category/example-title',
                    'is_active'     => true,
                ]
            ]
        ]);

        $this->assertEquals(['Offer', 'Category'], $viewModel->ancestorsNames());
    }

    /** @test */
    public function shouldBeAbleToGetThumbnail()
    {
        $viewModel = new ContentViewModel([
            'thumb' => [
                'type'      => [
                    'name' => 'image'
                ],
                'name'      => 'example',
                'extension' => 'jpg',
                'size'      => 250899,
                'mime_type' => 'image/jpeg',
                'is_active' => true
            ]
        ]);

        $this->assertEquals('example', $viewModel->thumbnail()->name());
        $this->assertEquals('jpg', $viewModel->thumbnail()->extension());
        $this->assertEquals('image/jpeg', $viewModel->thumbnail()->mimeType());
        $this->assertEquals(250899, $viewModel->thumbnail()->size());
        $this->assertEquals('images/example.jpg', $viewModel->thumbnail()->uploadPath());
        $this->assertTrue($viewModel->thumbnail()->isActive());
    }
}
