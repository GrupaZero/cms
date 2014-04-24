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

use Gzero\Entity\Content;
use Gzero\Entity\ContentType;
use Mockery as M;

class ContentTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        M::close();
    }

    /**
     * @test
     */
    public function is_instantiable()
    {
        $this->assertInstanceOf('Gzero\Entity\Content', new Content(new ContentType('xxx')));
    }

//    /**
//     * @test
//     */
//    public function can_get_and_set_type()
//    {
//        $type    = M::mock('Gzero\Entity\ContentType');
//        $type2   = M::mock('Gzero\Entity\ContentType');
//        $content = new Content($type); // on constructor
//        $this->assertSame($type, $content->getType());
//        $content->setType($type2); // with setter
//        $this->assertSame($type2, $content->getType());
//    }

}

