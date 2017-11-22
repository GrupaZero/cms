<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\Repositories\ContentReadRepository;

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
        $content = $this->tester->haveContent([
            'type'         => 'content',
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title'
                ]
            ]
        ]);

        $contentFromDb  = $this->repository->getByPath('example-title', 'en');
        $translations = $contentFromDb->route->translations->first();

        $this->assertEquals($content->id, $contentFromDb->id);
        $this->assertEquals('example-title', $translations->path);
        $this->assertEquals('en', $translations->language_code);
    }
}

