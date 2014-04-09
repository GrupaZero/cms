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

namespace tests\Entity;


use Gzero\Entity\Block;
use Gzero\Entity\BlockType;
use Mockery as M;

class BlockTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        M::close();
    }

    /**
     * @test
     */
    public function is_instantiable()
    {
        $this->assertInstanceOf('Gzero\Entity\Block', new Block(new BlockType('dummy')));
    }

    /**
     * @test
     */
    public function can_get_and_set_type()
    {
        $type  = M::mock('Gzero\Entity\BlockType');
        $type2 = M::mock('Gzero\Entity\BlockType');
        $block = new Block($type); // on constructor
        $this->assertSame($type, $block->getType());
        $block->setType($type2); // with setter
        $this->assertSame($type2, $block->getType());
    }

    /**
     * @test
     */
    public function can_add_and_get_translation()
    {
        $translation = M::mock('Gzero\Entity\BlockTranslation')->shouldReceive('setBlock')->once()->getMock();
        $block       = new Block(M::mock('Gzero\Entity\BlockType'));
        $block->addTranslation($translation);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $block->getTranslations());
        $this->assertSame($translation, $block->getTranslations()->first());
    }
}

