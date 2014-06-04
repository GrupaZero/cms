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
use Mockery as M;

class ContentTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        M::close();
    }

    /** @test */
    public function is_instantiable()
    {
        $this->assertInstanceOf('Gzero\Entity\Content', new Content(M::mock('Gzero\Entity\ContentType')));
    }

    /** @test */
    public function add_get_translation()
    {
        $translation = M::mock('Gzero\Entity\ContentTranslation');
        $content     = new Content(M::mock('Gzero\Entity\ContentType'));
        $content->addTranslation($translation);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $content->getTranslations());
        $this->assertSame($translation, $content->getTranslations()->first());
    }
}
