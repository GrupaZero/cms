<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class LangRepositoryTest
 *
 * @package    tests\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests\Repository;

use Gzero\Entity\Lang;
use Gzero\Repository\LangRepository;
use Mockery as M;


class LangRepositoryTest extends \Doctrine2TestCase {

    /**
     * @var LangRepository
     */
    private $repo;

    function setUp()
    {
        parent::setUp();
        // We're mocking Laravel cache in init function because we don't want to use Doctrine 2 constructors
        $cache = M::mock('Illuminate\Cache\Repository');
        $cache->shouldReceive('get')->andReturn(null)
            ->shouldReceive('forever');
        $this->exampleData();
        $this->repo = $this->em->getRepository('Gzero\Entity\Lang');
        // LangRepository is a special kind of repository, we use is as a singleton
        $this->repo->init($cache);
    }

    /** @test */
    function is_the_repository_works()
    {
        $this->assertEquals(3, count($this->repo->findAll()));
        $this->assertNotEmpty($this->repo->getByCode('en'));
    }

    /** @test */
    function can_get_all_languages()
    {
        $this->assertCount(3, $this->repo->getAll());
    }

    /** @test */
    function can_get_enabled_languages()
    {
        $this->assertCount(2, $this->repo->getAllEnabled());
    }

    /**
     * Generate example data for this test
     */
    private function exampleData()
    {
        $lang  = new Lang('en', 'en_US');
        $lang2 = new Lang('pl', 'pl_PL');
        $lang3 = new Lang('de', 'de_DE');
        $lang->setIsEnabled(true);
        $lang2->setIsEnabled(true);
        $lang2->setIsDefault(true);
        $lang3->setIsEnabled(false);
        $this->em->persist($lang);
        $this->em->persist($lang2);
        $this->em->persist($lang3);
        $this->em->flush();
    }
}
