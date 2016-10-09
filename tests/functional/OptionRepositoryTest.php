<?php namespace functional;

use Gzero\Entity\Lang;
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

    protected $expectedOptions;

    public function setUp()
    {
        parent::setUp();

        $this->recreateRepository();
        $this->setExpectedOptions();
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
     * @expectedExceptionMessage Option nonexistent option in category general does not exist
     */
    public function it_checks_existence_of_option_when_getting_an_option()
    {
        $this->repository->getOption('general', 'nonexistent option');
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
     * @expectedExceptionMessage Option nonexistent option in category general does not exist
     */
    public function it_checks_existence_of_option_when_deleting_an_option()
    {
        $this->repository->deleteOption('general', 'nonexistent option');
    }

    /**
     * @test
     */
    public function it_gets_general_option()
    {
        self::assertEquals(
            $this->expectedOptions['general']['siteName']['en'],
            $this->repository->getOption('general', 'siteName')['en']
        );
    }

    /**
     * @test
     */
    public function it_gets_general_options()
    {

        self::assertEquals(
            $this->expectedOptions['general'],
            $this->repository->getOptions('general')
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
        $mainCategory = 'general';
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
        $this->repository->deleteCategory('general');
        self::assertNull(OptionCategory::find('general'));
    }

    /**
     * @test
     */
    public function can_delete_option()
    {
        $this->repository->deleteOption('general', 'siteName');
        self::assertFalse(
            OptionCategory::find('general')->options()->
            where(['key' => 'siteName'])->exists()
        );
    }
    
    private function recreateRepository()
    {
        $this->repository = new OptionRepository(new OptionCategory(), new Option(), new \Illuminate\Cache\CacheManager($this->app));
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
