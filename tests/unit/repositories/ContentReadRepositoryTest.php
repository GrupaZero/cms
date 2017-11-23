<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Repositories\ContentReadRepository;
use Gzero\Core\Models\Language;
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

    /** @test */
    public function getAncestorsTitlesAndPaths()
    {
        $language    = Language::find(['code' => 'en'])->first();
        $grandParent = $this->tester->haveContent([
            'type'         => 'category',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Grand parent title'
                ]
            ]
        ]);
        $parent      = $this->tester->haveContent([
            'type'         => 'category',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Parent title'
                ]
            ]
        ]);
        $child       = $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Child title'
                ]
            ]
        ]);

        $parent->setChildOf($grandParent);
        $child->setChildOf($parent);

        $titles = $this->repository->getAncestorsTitlesAndPaths($child, $language)->toArray();

        $this->assertEquals('Grand parent title', $titles[0]->title);
        $this->assertEquals('grand-parent-title', $titles[0]->path);
        $this->assertEquals("$grandParent->id/", $grandParent->path);

        $this->assertEquals('Parent title', $titles[1]->title);
        $this->assertEquals('parent-title', $titles[1]->path);
        $this->assertEquals("$grandParent->id/$parent->id/", $parent->path);

        $this->assertEquals('Child title', $titles[2]->title);
        $this->assertEquals('child-title', $titles[2]->path);
        $this->assertEquals("$grandParent->id/$parent->id/$child->id/", $child->path);
    }
}

