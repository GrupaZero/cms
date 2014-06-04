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


use Gzero\Core\BlockHandler;
use Gzero\Entity\Block;
use Gzero\Entity\BlockType;
use Gzero\Entity\Lang;
use Gzero\Repository\BlockRepository;
use Mockery as M;

class BlockTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        M::close();
    }

    /** @test */
    public function is_instantiable()
    {
        $this->assertInstanceOf('Gzero\Entity\Block', new Block(new BlockType('dummy')));
    }

    /** @test */
    public function can_add_and_get_translation()
    {
        $translation = M::mock('Gzero\Entity\BlockTranslation');
        $block       = new Block(M::mock('Gzero\Entity\BlockType'));
        $block->addTranslation($translation);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $block->getTranslations());
        $this->assertSame($translation, $block->getTranslations()->first());
    }
}

