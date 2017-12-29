<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\ViewModels\BlockViewModel;
use Gzero\Core\Models\User;

class BlockPresenterTest extends Unit {

    /** @test */
    public function canInstantiate()
    {
        $this->assertInstanceOf(BlockViewModel::class, new BlockViewModel([]));
    }

    /** @test */
    public function canAccessArrayValuesAsObjectProperties()
    {
        $user      = factory(User::class)->create(['name' => 'John Doe']);
        $presenter = new BlockViewModel([
            'id'           => 1,
            'region'       => 'Example region',
            'theme'        => 'is-sticky',
            'options'      => 'Example options',
            'weight'       => 10,
            'is_active'    => true,
            'is_cacheable' => true,
            'author'       => $user->toArray(),
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
        $this->assertEquals(1, $presenter->id());
        $this->assertEquals('Example region', $presenter->region());
        $this->assertEquals('Example options', $presenter->options());
        $this->assertEquals('Example title', $presenter->title());
        $this->assertEquals('Example body', $presenter->body());
        $this->assertEquals('is-sticky', $presenter->theme());
        $this->assertEquals('John Doe', $presenter->author()->displayName());
    }
}
