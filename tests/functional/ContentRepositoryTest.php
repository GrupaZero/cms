<?php namespace functional;

use Gzero\Entity\Content;
use Gzero\Entity\User;
use Gzero\Repository\ContentRepository;
use Illuminate\Events\Dispatcher;

require_once(__DIR__ . '/../TestCase.php');
require_once(__DIR__ . '/../stub/TestSeeder.php');

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
class ContentRepositoryTest extends \TestCase {

    /**
     * @var ContentRepository
     */
    protected $repository;

    public function setUp()
    {
        parent::setUp();
        $this->repository = new ContentRepository(new Content(), new Dispatcher());
        $this->app['artisan']->call('db:seed', ['--class' => 'TestSeeder']); // Relative to tests/app/
    }

    /**
     * @test
     */
    public function is_works()
    {
        $this->repository->create(
            [
                'type'         => 'content',
                'translations' => [
                    'langCode' => 'en',
                    'title'    => 'lol'
                ]
            ],
            new User()
        );
    }
}
