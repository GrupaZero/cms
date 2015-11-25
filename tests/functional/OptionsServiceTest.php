<?php namespace functional;

use TestSeeder;
use Gzero\Entity\Option;
use Gzero\Entity\OptionCategory;
use Gzero\Repository\OptionRepository;
use Gzero\Core\OptionsService;

require_once(__DIR__ . '/../stub/TestSeeder.php');
require_once(__DIR__ . '/../stub/TestTreeSeeder.php');

class OptionsServiceTest extends \EloquentTestCase {

    /**
     * @var OptionRepository
     */
    protected $repository;

    /**
     * @var OptionsService
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();

        $this->seed('TestSeeder');
        $this->recreateRepository();
        $this->service = new OptionsService($this->repository);
    }

    /**
     * @test
     */
    public function can_get_categories()
    {
        $categories = $this->service->getCategories();

        $this->assertEquals(
            [
                0 => 'main'
            ],
            $categories
        );
    }

    /**
     * @test
     */
    public function can_get_options_from_given_category()
    {
        $options = $this->service->getOptions('main');

        $this->assertEquals(
            [
                'standard'  => 'cisco',
                'standard2' => 'microsoft'
            ],
            $options
        );
    }

    /**
     * @test
     */
    public function can_get_single_option()
    {
        $option = $this->service->getOption('main', 'standard');

        $this->assertEquals('cisco', $option);
    }

    private function recreateRepository()
    {
        $this->repository = new OptionRepository(
            new OptionCategory(),
            new Option(),
            new \Illuminate\Cache\CacheManager($this->app)
        );
    }

}
