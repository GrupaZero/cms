<?php namespace functional;

use Gzero\Entity\Lang;
use Gzero\Entity\Option;
use Gzero\Entity\OptionCategory;
use Gzero\Repository\OptionRepository;

require_once(__DIR__ . '/../../stub/TestSeeder.php');
require_once(__DIR__ . '/../../stub/TestTreeSeeder.php');

class OptionRepositoryTest extends \TestCase {

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var OptionRepository
     */
    protected $repository;

    protected $expectedOptions;

    protected function _before()
    {
        // Start the Laravel application
        $this->startApplication();
        $this->recreateRepository();
        $this->setExpectedOptions();
    }

    public function _after()
    {
        // Stop the Laravel application
        $this->stopApplication();
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Category nonexistent category does not exist
     */
    public function it_checks_existence_of_category_when_getting_an_option()
    {
        $categoryName = 'nonexistent category';

        $this->repository->getOptions($categoryName);

        $this->tester->dontSeeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Category nonexistent category does not exist
     */
    public function it_checks_existence_of_category_and_option_when_getting_an_non_existing_option()
    {
        $optionName   = 'nonexistent option';
        $categoryName = 'nonexistent category';

        $this->repository->getOption($categoryName, $optionName);

        $this->tester->dontSeeInDatabase('Options', ['key' => $optionName]);
        $this->tester->dontSeeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Option nonexistent option in category general does not exist
     */
    public function it_checks_existence_of_option_when_getting_an_option_from_existing_category()
    {
        $optionName   = 'nonexistent option';
        $categoryName = 'general';

        $this->repository->getOption($categoryName, $optionName);

        $this->tester->dontSeeInDatabase('Options', ['key' => $optionName]);
        $this->tester->seeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Category nonexistent category does not exist
     */
    public function it_checks_existence_of_category_when_deleting_an_option()
    {
        $categoryName = 'nonexistent category';

        $this->repository->deleteCategory($categoryName);

        $this->tester->dontSeeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Category nonexistent category does not exist
     */
    public function it_checks_existence_of_category_and_option_when_deleting_an_non_existing_option()
    {
        $optionName   = 'nonexistent option';
        $categoryName = 'nonexistent category';

        $this->repository->deleteOption($categoryName, $optionName);

        $this->tester->dontSeeInDatabase('Options', ['key' => $categoryName]);
        $this->tester->dontSeeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Option nonexistent option in category general does not exist
     */
    public function it_checks_existence_of_option_when_deleting_an_option()
    {
        $optionName   = 'nonexistent option';
        $categoryName = 'general';

        $this->repository->deleteOption($categoryName, $optionName);

        $this->tester->dontSeeInDatabase('Options', ['key' => $optionName]);
        $this->tester->seeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     */
    public function it_gets_option_from_general_category()
    {
        $optionName   = 'siteName';
        $categoryName = 'general';

        $this->assertEquals(
            $this->expectedOptions[$categoryName][$optionName]['en'],
            $this->repository->getOption($categoryName, $optionName)['en']
        );

        $this->tester->seeInDatabase('Options', ['key' => $optionName]);
        $this->tester->seeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     */
    public function it_gets_all_options_from_general_category()
    {
        $categoryName = 'general';

        $this->assertEquals(
            $this->expectedOptions[$categoryName],
            $this->repository->getOptions($categoryName)
        );

        $this->tester->seeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     */
    public function can_create_category()
    {
        $categoryName = 'New category';

        $this->repository->createCategory($categoryName);

        $this->assertNotNull(OptionCategory::find($categoryName));

        $this->tester->seeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     */
    public function can_create_option()
    {
        $categoryName = 'general';
        $optionName   = 'some option';
        $value        = ['en' => 'new option value'];

        $this->repository->updateOrCreateOption($categoryName, $optionName, $value);

        $savedOption = OptionCategory::find($categoryName)->options()->where(['key' => $optionName])->first();
        $this->assertNotNull($savedOption);
        $this->assertEquals($value, $savedOption->value);

        $this->recreateRepository();
        $this->assertEquals($value, $this->repository->getOption($categoryName, $optionName));

        $this->tester->seeInDatabase('Options', ['key' => $optionName]);
        $this->tester->seeInDatabase('OptionCategories', ['key' => $categoryName]);
    }


    /**
     * @test
     */
    public function can_delete_category()
    {
        $categoryName = 'general';

        $this->repository->deleteCategory($categoryName);
        $this->assertNull(OptionCategory::find($categoryName));

        $this->tester->dontSeeInDatabase('OptionCategories', ['key' => $categoryName]);
    }

    /**
     * @test
     */
    public function can_delete_option()
    {
        $categoryName = 'general';
        $optionName   = 'siteName';

        $this->repository->deleteOption('general', $optionName);
        $this->assertFalse(
            OptionCategory::find($categoryName)->options()->
            where(['key' => $optionName])->exists()
        );

        $this->tester->dontSeeInDatabase('Options', ['key' => $optionName]);
    }

    private function recreateRepository()
    {
        $this->repository = new OptionRepository(
            new OptionCategory(),
            new Option(),
            new \Illuminate\Cache\CacheManager($this->app)
        );
    }

    private function setExpectedOptions()
    {
        $this->expectedOptions = [
            'general' => [
                'siteName'         => [],
                'siteDesc'         => [],
                'defaultPageSize'  => [],
                'cookiesPolicyUrl' => [],
            ],
            'seo'     => [
                'seoDescLength'     => [],
                'googleAnalyticsId' => [],
            ]
        ];

        // Propagate Lang options based on gzero config
        foreach ($this->expectedOptions as $categoryKey => $category) {
            foreach ($this->expectedOptions[$categoryKey] as $key => $option) {
                foreach (Lang::all()->toArray() as $lang) {
                    $this->expectedOptions[$categoryKey][$key][$lang['code']] = config('gzero.' . $key);
                }
            }
        }
    }
}
