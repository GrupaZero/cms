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
use Gzero\Entity\Content;
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
        // Content
        $contentType = $this->getType('Gzero\Entity\ContentType', 'category');
        $content     = new Content($contentType);
        $content2    = new Content($contentType);
        $content->setPath('/s/ds/ds/d/');
        $content2->setPath('/x/x');
        $content->setChildOf($content2);
        $this->em->persist($content);
        $this->em->flush();

        // Block
        $blockType = $this->getType('Gzero\Entity\BlockType', 'basic');
        $block     = new Block($blockType);
        $block->setRegions(['footer', 'header']);
        $lang = $this->em->find('Gzero\Entity\Lang', 'pl');
        if (!$lang) {
            $lang = new Lang('pl', 'pl_PL');
            $this->em->persist($lang);
        }
        $translation = new BlockTranslation($block, $lang);
        $translation->setTitle('test');
        $block->addTranslation($translation);
        $this->em->persist($block);
        $this->em->flush();
        // Temporary solution for checking doctrine 2 table creation
    }

    /**
     * @param $entityName
     * @param $typeName
     *
     * @return BlockType|null|object
     */
    protected function getType($entityName, $typeName)
    {
        $type = $this->em->find($entityName, $typeName);
        if (!$type) {
            $type = new $entityName($typeName);
            $this->em->persist($type);
            return $type;
        }
        return $type;
    }
}
