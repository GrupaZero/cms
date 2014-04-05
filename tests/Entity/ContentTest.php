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

class ContentTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function is_instantiable()
    {
        $this->assertInstanceOf('Gzero\Entity\Content', new Content());
    }

    /**
     * @test
     * @expectedException \PHPUnit_Framework_Error
     */
    public function can_get_and_set_type()
    {
        $content = new Content();
        $this->assertInstanceOf('Gzero\Entity\ContentType', new ContentType());
        $type = new ContentType();
        $content->setType($type);
        $this->assertEquals($type, $content->getType());
        $content->setType(new \stdClass()); // Wrong type set
    }

}

