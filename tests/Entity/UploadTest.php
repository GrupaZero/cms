<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class UploadTest
 *
 * @package    tests\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests\Entity;

use Gzero\Entity\Upload;
use Gzero\Entity\UploadType;
use Mockery as M;

class UploadTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        M::close();
    }

    /**
     * @test
     */
    public function is_instantiable()
    {
        $this->assertInstanceOf('Gzero\Entity\Upload', new Upload(new UploadType('image')));
    }

    /**
     * @test
     */
    public function can_get_and_set_type()
    {
        $type   = M::mock('Gzero\Entity\UploadType');
        $type2  = M::mock('Gzero\Entity\UploadType');
        $upload = new Upload($type);
        $this->assertSame($type, $upload->getType());
        $upload->setType($type2); // with setter
        $this->assertSame($type2, $upload->getType());
    }

    /**
     * @test
     */
    public function can_add_and_get_translation()
    {
        $type    = M::mock('Gzero\Entity\UploadType');
        $upload  = new Upload($type);
        $storage = [];
        for ($i = 0; $i < 3; $i++) {
            $translation = m::mock('Gzero\Entity\UploadTranslation')->shouldReceive('setUpload')->getMock();
            $storage[]   = $translation;
            $upload->addTranslation($translation);
        }
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $upload->getTranslations());
        $this->assertCount(3, $upload->getTranslations());
        $this->assertSame($storage[1], $upload->getTranslations()->get(1));
    }

}
