<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class DummyTest
 *
 * @package    tests
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests;

use Gzero\Entity\Block;
use Gzero\Entity\BlockTranslation;
use Gzero\Entity\BlockType;
use Gzero\Entity\Lang;

class DummyTest extends \Doctrine2TestCase {

    public function setUp()
    {
        $this->dbParams = array(
            'driver'   => 'pdo_mysql',
            'user'     => 'doctrine2',
            'password' => 'test',
            'dbname'   => 'doctrine2',
        );
        parent::setUp();
    }

    /**
     * @test
     * @group ignore
     */
    public function dummy()
    {
        $type = $this->em->find('Gzero\Entity\BlockType', 'normal');
        if (!$type) {
            $type = new BlockType('normal');
            $this->em->persist($type);
        }
        $block = new Block($type);
        $block->setRegion(['footer', 'header']);
        $lang = $this->em->find('Gzero\Entity\Lang', 'pl');
        if (!$lang) {
            $lang = new Lang('pl', 'pl_PL');
            $this->em->persist($lang);
        }
        $translation = new BlockTranslation('Test', $lang);
        $translation->setBlock($block);
        $block->addTranslation($translation);
        $this->em->persist($block);
        $this->em->flush();
        // Temporary solution for checking doctrine 2 table creation
    }
}
