<?php namespace Cms;

use Carbon\Carbon;
use Codeception\Test\Unit;
use Gzero\Cms\Presenters\BlockPresenter;
use Gzero\Core\Models\User;

class BlockPresenterTest extends Unit {

    /** @test */
    public function canInstantiate()
    {
        $this->assertInstanceOf(BlockPresenter::class, new BlockPresenter([]));
    }

    /** @test */
    public function canAccessArrayValuesAsObjectProperties()
    {
        $user      = factory(User::class)->create(['name' => 'John Doe']);
        $presenter = new BlockPresenter([
            'id'           => 1,
            'region'       => 'Example region',
            'theme'        => 'is-sticky',
            'options'      => 'Example options',
            'weight'       => 10,
            'is_active'    => true,
            'is_cacheable' => true,
            'author'       => $user,
            'translations' => [
                [
                    'language_code' => 'en',
                    'title'         => 'Example title',
                    'body'          => 'Example body',
                ]
            ]
        ]);

        $this->assertTrue($presenter->isActive());
        $this->assertTrue($presenter->isCacheable());
        $this->assertEquals(1, $presenter->getId());
        $this->assertEquals('Example region', $presenter->getRegion());
        $this->assertEquals('Example options', $presenter->getOptions());
        $this->assertEquals('Example title', $presenter->getTitle());
        $this->assertEquals('Example body', $presenter->getBody());
        $this->assertEquals('is-sticky', $presenter->getTheme());
        $this->assertEquals('John Doe', $presenter->getAuthor()->displayName());
    }
}
