<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class BlockTest
 *
 * @package    tests\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests\Core;


use Gzero\Core\BlockHandler;
use Gzero\Entity\Block;
use Gzero\Entity\BlockType;
use Gzero\Entity\Lang;
use Mockery as M;

class BlockHandlerTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        M::close();
    }

    /** @test */
    public function can_load_all_active_blocks()
    {
        //Mocking blocks of different types
        $block1 = new Block(new BlockType('basic'));
        $block1->setRegions(['footer']);
        $block2 = new Block(new BlockType('slider'));
        $block2->setRegions(['header']);

        $cache     = M::mock('Illuminate\Cache\Repository');
        $blockRepo = M::mock('Gzero\Repository\BlockRepository')
            ->shouldReceive('getAllActive')
            ->andReturn(
                [
                    $block1,
                    $block2
                ]
            )
            ->getMock();
        $handler   = M::mock('DummyHandler'); //DummyHandler for build() method
        $handler->shouldReceive('load->render');
        $app          = M::mock('Illuminate\Foundation\Application')
            ->shouldReceive('make')->times(1)->with('block_type:basic')->andReturn($handler)
            ->shouldReceive('make')->times(1)->with('block_type:slider')->andReturn($handler)
            ->getMock();
        $blockHandler = new BlockHandler($cache, $app, $blockRepo);
        //Loads and build blocks
        $blockHandler->loadAllActive('/', new Lang('pl', 'pl_PL'));
        //Getting blocks regions
        $regions = $blockHandler->getRegions()->toArray();
        //Checking regions match mocked blocks
        $this->assertArrayHasKey('footer', $regions);
        $this->assertArrayHasKey('header', $regions);
    }
}

