<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockRepositoryTest
 *
 * @package    tests\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests\Repository;

use Gzero\Entity\Block;
use Gzero\Entity\BlockType;

class BlockRepositoryTest extends \Doctrine2TestCase {

    public function setUp()
    {
        parent::setUp();
        $this->exampleData();
    }

    /** @test */
    public function is_the_repository_works()
    {
        $repo = $this->em->getRepository('Gzero\Entity\Block');
        $this->assertEquals(10, count($repo->findAll()));
        $this->assertEquals(1, count($repo->findBy(['id' => 5])));
    }

    /**
     * Generate example data for this test
     */
    private function exampleData()
    {
        $type = new BlockType('normal');
        for ($i = 0; $i < 10; $i++) {
            $block = new Block($type);
            $block->setActive(rand(0, 1));
            $this->em->persist($block);
        }
        $this->em->persist($type);
        $this->em->flush();
    }
}
