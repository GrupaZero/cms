<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Models\Route;
use Gzero\Core\Models\RouteTranslation;

class ContentReadRepositoryTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var ContentReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new ContentReadRepository();
    }

    /** @test */
    public function canGetContentByPath()
    {
        $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                    'is_active'     => false
                ]
            ]
        ]);

        // inactive route translations as default
        $content          = $this->repository->getByPath('example-title', 'en');
        $routeTranslation = $content->route->translations(false)->first();

        $this->assertEquals('example-title', $routeTranslation->path);
        $this->assertEquals('en', $routeTranslation->language_code);
        // Only active route translations
        $this->assertNull($this->repository->getByPath('example-title', 'en', true));

    }
}

