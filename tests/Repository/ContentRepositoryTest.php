<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ContentRepositoryTest
 *
 * @package    tests\Repository
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests\Repository;

use Gzero\Entity\Content;
use Gzero\Entity\ContentType;

class ContentRepositoryTest extends \Doctrine2TestCase {

    function setUp()
    {
        parent::setUp();
        $this->exampleData();
    }

    /** @test */
    function is_the_repository_works()
    {
        $repo    = $this->em->getRepository('Gzero\Entity\Content');
        $content = $repo->getById(3);
        $this->assertEquals(1, count($content));
        $this->assertEquals(10, count($repo->findAll()));
    }

    /**
     * Generate example data for this test
     */
    private function exampleData()
    {
        $types      = [
            'page',
            'category',
            'product'
        ];
        $typesTable = [];
        foreach ($types as $type) {
            $newType      = new ContentType($type);
            $typesTable[] = $newType;
            $this->em->persist($newType);
        }
        for ($i = 0; $i < 10; $i++) {
            $content = new Content($typesTable[rand(0, count($typesTable) - 1)]);
            $this->em->persist($content);
        }
        $this->em->flush();
    }
}
