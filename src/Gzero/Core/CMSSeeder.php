<?php namespace Gzero\Core;

use Gzero\Entity\Block;
use Gzero\Entity\BlockTranslation;
use Gzero\Entity\BlockType;
use Gzero\Entity\Content;
use Gzero\Entity\ContentTranslation;
use Gzero\Entity\Lang;
use Gzero\Entity\MenuLink;
use Gzero\Entity\MenuLinkTranslation;
use Gzero\Entity\User;
use Illuminate\Database\Seeder;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class CMSSeeder
 *
 * @package    Gzero\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 * @SuppressWarnings("PHPMD")
 */
class CMSSeeder extends Seeder {

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @SuppressWarnings("PHPMD")
     */
    public function run()
    {
        $lang = $this->em->find('Gzero\Entity\Lang', 'pl');
        if (!$lang) {
            $lang = new Lang('pl', 'pl_PL');
            $lang->setIsEnabled(TRUE);
            $this->em->persist($lang);
        }

        $lang2 = $this->em->find('Gzero\Entity\Lang', 'en');
        if (!$lang2) {
            $lang2 = new Lang('en', 'en_US');
            $lang2->setIsEnabled(TRUE);
            $this->em->persist($lang2);
        }

        // Content
        $contentType = $this->getType('Gzero\Entity\ContentType', 'category');
        $content     = new Content($contentType);
        $content2    = new Content($contentType);
        $content->setActive(TRUE);
        $content2->setActive(TRUE);
        $content->setChildOf($content2);
        $this->em->persist($content);
        $this->em->flush();

        $contentTranslation = new ContentTranslation($content, $lang);
        $contentTranslation->setUrl('dummy-content');
        $contentTranslation->setTitle('Dummy content title');
        $contentTranslation->setBody(
            'Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. '
        );
        $contentTranslation->setActive(TRUE);
        $contentTranslation2 = new ContentTranslation($content2, $lang);
        $contentTranslation2->setUrl('dummy-content2');
        $contentTranslation2->setTitle('Dummy content2 title');
        $contentTranslation2->setBody(
            'Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. '
        );
        $contentTranslation2->setActive(TRUE);
        $content->addTranslation($contentTranslation);
        $content->addTranslation($contentTranslation2);

        // Menu link
        $link  = new MenuLink();
        $link2 = new MenuLink();
        $link3 = new MenuLink();
        $link4 = new MenuLink();
        $link->setAsRoot();
        $link2->setChildOf($link);
        $link3->setChildOf($link2);
        $link4->setChildOf($link3);
        $translation = new MenuLinkTranslation($link, $lang);
        $translation->setUrl('/link/');
        $translation->setTitle('link');
        $translation->setActive(TRUE);
        $translation2 = new MenuLinkTranslation($link2, $lang);
        $translation2->setUrl('/link/link1');
        $translation2->setTitle('link1');
        $translation2->setActive(TRUE);
        $link->addTranslation($translation);
        $link->addTranslation($translation2);
        $this->em->persist($link);
        $this->em->flush();

        // Block
        $blockType = $this->getType('Gzero\Entity\BlockType', 'basic');
        $block     = new Block($blockType);
        $block->setRegions(['footer', 'header']);
        $block->setActive(TRUE);
        $blockType2 = $this->getType('Gzero\Entity\BlockType', 'menu');
        $block2     = new Block($blockType2);
        $block2->setRegions(['footer', 'header']);
        $block2->setMenu($link);
        $block2->setActive(TRUE);
        $translation = new BlockTranslation($block, $lang);
        $translation->setTitle('Test Block');
        $translation->setActive(TRUE);
        $translation->setBody(
            'Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. '
        );
        $block->addTranslation($translation);
        $translation2 = new BlockTranslation($block2, $lang);
        $translation2->setTitle('Test menu Block');
        $translation2->setActive(TRUE);
        $block2->addTranslation($translation2);
        $this->em->persist($block);
        $this->em->persist($block2);
        $this->em->flush();

        $user = $this->em->find('Gzero\Entity\User', 1);
        if (!$user) {
            $user = new User();
            $user->setFirstName('John');
            $user->setLastName('Doe');
            $user->setEmail('a@a.pl');
            $user->setPassword('test');
            $this->em->persist($user);
            $this->em->flush();
        }
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
