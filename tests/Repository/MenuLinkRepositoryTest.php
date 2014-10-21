<?php namespace tests\Repository;

use Gzero\Doctrine2Extensions\Tree\TreeRepository;
use Gzero\Doctrine2Extensions\Tree\TreeRepositoryTrait;
use Gzero\Entity\MenuLink;
use Gzero\Entity\MenuLinkTranslation;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class MenuLinkRepositoryTest
 *
 * @package    tests\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class MenuLinkRepositoryTest extends \Doctrine2TestCase implements TreeRepository {

    use TreeRepositoryTrait;

    function setUp()
    {
        parent::setUp();
        $this->exampleData();
    }

    /** @test */
    function is_the_repository_works()
    {
        $repo = $this->em->getRepository('Gzero\Entity\MenuLink');
        $this->assertEquals(10, count($repo->findAll()));
        $this->assertEquals(1, count($repo->findBy(['id' => 5])));
    }

    /** @test */
    function can_add_new_menu_link()
    {
        $repo = $this->em->getRepository('Gzero\Entity\MenuLink');
        $repo->create(new MenuLink());
        $repo->commit();
        $this->assertEquals(11, count($repo->findAll())); // +1 with new link
    }

    /**
     * Generate example data for this test
     */
    private function exampleData()
    {
        $menuLink = new MenuLink();
        for ($i = 0; $i < 10; $i++) {
            $menuLink = new MenuLink();
            $this->em->persist($menuLink);
        }
        $this->em->persist($menuLink);
        $this->em->flush();
    }
} 
