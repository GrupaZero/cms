<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Cms\ViewModels\BlockViewModel;
use Gzero\Core\Models\User;

class BlockViewModelTest extends Unit {

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
                    'custom_fields' => 'custom fields'
                ]
            ]
        ]);

        $this->assertTrue($presenter->isActive());
        $this->assertTrue($presenter->isCacheable());
        $this->assertEquals(1, $presenter->id());
        $this->assertEquals(10, $presenter->weight());
        $this->assertEquals('Example region', $presenter->region());
        $this->assertEquals('Example options', $presenter->options());
        $this->assertEquals('Example title', $presenter->title());
        $this->assertEquals('Example body', $presenter->body());
        $this->assertEquals('is-sticky', $presenter->theme());
        $this->assertEquals('custom fields', $presenter->customFields());
        $this->assertEquals('John Doe', $presenter->author()->displayName());
    }

    /** @test */
    public function shouldBeAbleToGetTitleInSpecifiedLanguage()
    {
        $viewModel = new BlockViewModel([
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
        $viewModel = new BlockViewModel([
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
    public function shouldBeAbleToGetCustomFieldsInSpecifiedLanguage()
    {
        $viewModel = new BlockViewModel([
            'translations' => [
                [
                    'language_code' => 'en',
                    'custom_fields' => 'custom fields'
                ],
                [
                    'language_code' => 'pl',
                    'custom_fields' => 'pola niestandardowe'
                ]
            ]
        ]);

        $this->assertEquals('custom fields', $viewModel->customFields());
        $this->assertEquals('pola niestandardowe', $viewModel->customFields('pl'));
    }
}
