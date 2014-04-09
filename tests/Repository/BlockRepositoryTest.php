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

    /**
     * @test
     */
    public function it_works()
    {
        $repo = $this->em->getRepository('Gzero\Entity\Block');
        $this->assertEquals(10, count($repo->findAll()));
    }

    private function exampleData()
    {
        $type = new BlockType('normal');
        for ($i = 0; $i < 10; $i++) {
            $block = new Block($type);
            $this->em->persist($block);
        }
        $this->em->persist($type);
        $this->em->flush();
    }
}
