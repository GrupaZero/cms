<?php namespace functional;

use TestSeeder;
use Gzero\Entity\Option;
use Gzero\Entity\OptionCategory;
use Gzero\Repository\OptionRepository;


require_once(__DIR__ . '/../stub/TestSeeder.php');
require_once(__DIR__ . '/../stub/TestTreeSeeder.php');

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ContentRepositoryTest
 *
 * @package    functional
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class OptionRepositoryTest extends \EloquentTestCase {

    /**
     * @var OptionRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();

        $this->seed('TestSeeder');
        $this->recreateRepository();
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Category nonexistent category does not exist
     */
    public function it_checks_existence_of_category_when_getting_an_option()
    {
        $this->repository->getOptions('nonexistent category');
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Category nonexistent category does not exist
     */
    public function it_checks_existence_of_category_and_option_when_getting_an_non_existing_option()
    {
        $this->repository->getOption('nonexistent category', 'nonexistent option');
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Option nonexistent option in category main does not exist
     */
    public function it_checks_existence_of_option_when_getting_an_option()
    {
        $this->repository->getOption(TestSeeder::CATEGORY_MAIN, 'nonexistent option');
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Category nonexistent category does not exist
     */
    public function it_checks_existence_of_category_when_deleting_an_option()
    {
        $this->repository->deleteCategory('nonexistent category');
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Category nonexistent category does not exist
     */
    public function it_checks_existence_of_category_and_option_when_deleting_an_non_existing_option()
    {
        $this->repository->deleteOption('nonexistent category', 'nonexistent option');
    }

    /**
     * @test
     * @expectedException \Gzero\Repository\RepositoryException
     * @expectedExceptionMessage Option nonexistent option in category main does not exist
     */
    public function it_checks_existence_of_option_when_deleting_an_option()
    {
        $this->repository->deleteOption(TestSeeder::CATEGORY_MAIN, 'nonexistent option');
    }

    /**
     * @test
     */
    public function it_gets_standard_option()
    {
        self::assertEquals(
            TestSeeder::OPTION_VALUE_STANDARD,
            $this->repository->getOption(TestSeeder::CATEGORY_MAIN, TestSeeder::OPTION_STANDARD)
        );
    }

    /**
     * @test
     */
    public function it_gets_standard_options()
    {
        $retrievedOptions = $this->repository->getOptions(TestSeeder::CATEGORY_MAIN);

        $expectedOptions             = [
            TestSeeder::OPTION_STANDARD  => TestSeeder::OPTION_VALUE_STANDARD,
            TestSeeder::OPTION_STANDARD2 => TestSeeder::OPTION_VALUE_STANDARD2
        ];
        $retrievedNonexistentOptions = array_diff($retrievedOptions, $expectedOptions);
        self::assertEmpty(
            $retrievedNonexistentOptions,
            "retrieved nonexistent options: " . http_build_query($retrievedNonexistentOptions, '', ',')
        );
        $nonRetrievedOptions = array_diff($expectedOptions, $retrievedOptions);
        self::assertEmpty(
            $nonRetrievedOptions,
            "non retrieved options: " . http_build_query($nonRetrievedOptions, '', ',')
        );

    }

    /**
     * @test
     */
    public function can_create_category()
    {
        $categoryKey = 'another category';

        $this->repository->createCategory($categoryKey);
        self::assertNotNull(OptionCategory::find($categoryKey));
    }

    /**
     * @test
     */
    public function can_create_option()
    {
        $mainCategory = TestSeeder::CATEGORY_MAIN;
        $newOptionKey = 'some option';
        $value        = ['en' => 'new option value'];

        $this->repository->updateOrCreateOption($mainCategory, $newOptionKey, $value);

        $savedOption = OptionCategory::find($mainCategory)->options()->where(['key' => $newOptionKey])->first();
        self::assertNotNull($savedOption);
        self::assertEquals($value, $savedOption->value);

        $this->recreateRepository();
        self::assertEquals($value, $this->repository->getOption($mainCategory, $newOptionKey));
    }


    /**
     * @test
     */
    public function can_delete_category()
    {
        $this->repository->deleteCategory(TestSeeder::CATEGORY_MAIN);
        self::assertNull(OptionCategory::find(TestSeeder::CATEGORY_MAIN));
    }

    /**
     * @test
     */
    public function can_delete_option()
    {
        $this->repository->deleteOption(TestSeeder::CATEGORY_MAIN, TestSeeder::OPTION_STANDARD);
        self::assertFalse(
            OptionCategory::find(TestSeeder::CATEGORY_MAIN)->options()->
            where(['key' => TestSeeder::OPTION_STANDARD])->exists()
        );
    }
    
    private function recreateRepository()
    {
        $this->repository = new OptionRepository(new OptionCategory(), new Option(), new \Illuminate\Cache\CacheManager($this->app));
    }

}
