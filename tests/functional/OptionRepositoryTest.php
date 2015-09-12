<?php namespace functional;

use Gzero\Entity\OptionCategories;
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
        $this->repository = new OptionRepository(new OptionCategories(), new \Illuminate\Cache\CacheManager($this->app));
    }

    /**
     * @test
     */
    public function can_instantiate()
    {

    }
}
