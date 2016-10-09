<?php namespace functional;

use Gzero\Entity\Lang;
use Gzero\Entity\Option;
use Gzero\Entity\OptionCategory;
use Gzero\Repository\OptionRepository;
use Gzero\Core\OptionsService;

require_once(__DIR__ . '/../../stub/TestSeeder.php');
require_once(__DIR__ . '/../../stub/TestTreeSeeder.php');

class OptionsServiceTest extends \TestCase {

    /**
     * @var OptionRepository
     */
    protected $repository;

    /**
     * @var OptionsService
     */
    protected $service;

    protected $expectedOptions;

    protected function _before()
    {
        // Start the Laravel application
        $this->startApplication();
        $this->recreateRepository();
        $this->service = new OptionsService($this->repository);
        $this->setExpectedOptions();
    }

    public function _after()
    {
        // Stop the Laravel application
        $this->stopApplication();
    }

    /**
     * @test
     */
    public function can_get_categories()
    {
        $categories = $this->service->getCategories();

        $this->assertEquals(
            [
                0 => 'general',
                1 => 'seo'

            ],
            $categories
        );
    }

    /**
     * @test
     */
    public function can_get_options_from_given_category()
    {
        $options = $this->service->getOptions('general');

        $this->assertEquals($this->expectedOptions['general'], $options);
    }

    /**
     * @test
     */
    public function can_get_single_option()
    {
        $option = $this->service->getOption('general', 'siteName')['en'];

        $this->assertEquals($this->expectedOptions['general']['siteName']['en'], $option);
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
