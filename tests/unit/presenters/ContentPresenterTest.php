<?php namespace Cms;

use Carbon\Carbon;
use Codeception\Test\Unit;
use Gzero\Cms\Presenters\ContentPresenter;

class ContentPresenterTest extends Unit {

    /** @test */
    public function canInstantiate()
    {
        $this->assertInstanceOf(ContentPresenter::class, new ContentPresenter([]));
    }

    /** @test */
    public function canAccessArrayValuesAsObjectProperties()
    {
        $presenter = new ContentPresenter([
            'id'                 => 1,
            'theme'              => 'is-sticky',
            'weight'             => 10,
            'is_active'          => true,
            'is_on_home'         => true,
            'is_promoted'        => true,
            'is_sticky'          => true,
            'is_comment_allowed' => true,
            'published_at'       => Carbon::now(),
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

    }
}
