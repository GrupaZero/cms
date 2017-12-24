<?php namespace Cms;

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
            'theme'        => 'This is test',
            'is_sticky'    => true,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title'
                ]
            ]
        ]);

        $this->assertEquals('This is test', $presenter->theme);
        $this->assertTrue($presenter->isSticky());
        $this->assertEquals('Example title', $presenter->getTitle());

    }
}
