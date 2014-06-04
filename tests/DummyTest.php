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
use Gzero\Entity\ContentTranslation;
use Gzero\Entity\Lang;
use Gzero\Entity\MenuLink;
use Gzero\Entity\MenuLinkTranslation;

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
        $lang = $this->em->find('Gzero\Entity\Lang', 'pl');
        if (!$lang) {
            $lang = new Lang('pl', 'pl_PL');
            $this->em->persist($lang);
        }

        // Content
        $contentType = $this->getType('Gzero\Entity\ContentType', 'category');
        $content     = new Content($contentType);
        $content2    = new Content($contentType);
        $content->isActive();
        $content2->isActive();
        $content->setPath('/s/ds/ds/d/');
        $content2->setPath('/x/x');
        $content->setChildOf($content2);
        $this->em->persist($content);
        $this->em->flush();

        $contentTranslation = new ContentTranslation($content, $lang);
        $contentTranslation->setUrl('dummy-content');
        $contentTranslation->setTitle('Dummy content title');
        $contentTranslation2 = new ContentTranslation($content2, $lang);
        $contentTranslation2->setUrl('dummy-content2');
        $contentTranslation2->setTitle('Dummy content2 title');
        $content->addTranslation($contentTranslation);
        $content->addTranslation($contentTranslation2);

        // Menu link
        $link  = new MenuLink();
        $link2 = new MenuLink();
        $link->setAsRoot();
        $link->setPath('/link/');
        $link2->setPath('/link/link1');
        $link2->setChildOf($link);
        $translation = new MenuLinkTranslation($link, $lang);
        $translation->setUrl('/link/');
        $translation->setTitle('link');
        $translation2 = new MenuLinkTranslation($link2, $lang);
        $translation2->setUrl('/link/link1');
        $translation2->setTitle('link1');
        $link->addTranslation($translation);
        $link->addTranslation($translation2);
        $this->em->persist($link);
        $this->em->flush();

        // Block
        $blockType = $this->getType('Gzero\Entity\BlockType', 'basic');
        $block     = new Block($blockType);
        $block->setRegions(['footer', 'header']);
        $block->isActive();
        $blockType2 = $this->getType('Gzero\Entity\BlockType', 'menu');
        $block2     = new Block($blockType2);
        $block2->setRegions(['footer', 'header']);
        $block2->setMenu($link);
        $block2->isActive();
        $translation = new BlockTranslation($block, $lang);
        $translation->setTitle('test');
        $block->addTranslation($translation);
        $translation2 = new BlockTranslation($block2, $lang);
        $translation2->setTitle('menu test');
        $block2->addTranslation($translation2);
        $this->em->persist($block);
        $this->em->persist($block2);
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
