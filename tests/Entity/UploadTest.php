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
use \Mockery as m;

class UploadTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        m::close();
    }

    /**
     * @test
     */
    public function is_instantiable()
    {
        $this->assertInstanceOf('Gzero\Entity\Upload', new Upload());
    }

    /**
     * @test
     */
    public function can_get_and_set_type()
    {
        $upload = new Upload();
        $type   = m::mock('Gzero\Entity\UploadType');
        $upload->setType($type);
        $this->assertEquals($type, $upload->getType());
    }

    /**
     * @test
     */
    public function can_add_and_get_translation()
    {
        $upload  = new Upload();
        $storage = [];
        for ($i = 0; $i < 3; $i++) {
            $translation = m::mock('Gzero\Entity\UploadTranslation');
            $storage[]   = $translation;
            $upload->addTranslation($translation);
        }
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $upload->getTranslations());
        $this->assertCount(3, $upload->getTranslations());
        $this->assertSame($storage[1], $upload->getTranslations()->get(1));
    }


    /**
     * @test
     */
    public function can_find_exact_translation()
    {
        $upload  = new Upload();
        $storage = [];
        $langs   = ['pl', 'en', 'de'];
        for ($i = 0; $i < count($langs); $i++) {
            $translation = m::mock('Gzero\Entity\UploadTranslation');
            $translation->shouldReceive('getLangCode')->andReturn($langs[$i]);
            $storage[] = $translation;
            $upload->addTranslation($translation);
        }
        $this->assertSame($storage[1], $upload->findTranslation('en'));
    }
}
