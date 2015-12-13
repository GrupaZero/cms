<?php namespace unit;

use Gzero\Core\BlockFinder;
use Gzero\Entity\Block;
use Mockery as m;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockFinderTest
 *
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2015, Adrian Skierniewski
 */
class BlockFinderTest extends \TestCase {

    /**
     * @var \Gzero\Core\BlockFinder
     */
    protected $finder;

    /**
     * @var \Mockery\Mock
     */
    protected $repo;

    public function setUp()
    {
        parent::setUp();
        $this->repo   = m::mock('Gzero\Repository\BlockRepository');
        $this->finder = new BlockFinder($this->repo);
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function it_finds_correct_block()
    {
        $block1         = new Block();
        $block1->id     = 1;
        $block2         = new Block();
        $block2->id     = 2;
        $block2->filter = ['+' => ['1/*']];
        $this->repo->shouldReceive('getBlocks')->andReturn(
            [
                $block1,
                $block2,
            ]
        );
        $this->assertContains(2, $this->finder->getBlocksIds('1/2/'));
    }
}
