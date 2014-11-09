<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class LangTest
 *
 * @package    tests\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests\Entity;

use Gzero\Entity\Lang;
use Mockery as M;

class LangTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        M::close();
    }

    /** @test */
    public function is_instantiable()
    {
        $lang = new Lang('en', 'en_US');
        $this->assertInstanceOf('Gzero\Entity\Lang', $lang);
        $this->assertEquals($lang->getCode(), 'en');
        $this->assertEquals($lang->getI18n(), 'en_US');
    }

    /** @test */
    public function can_enable_and_disable()
    {
        $lang = new Lang('en', 'en_US');
        $lang->setIsEnabled(true);
        $this->assertTrue($lang->isEnabled());
        $lang->setIsEnabled(false);
        $this->assertFalse($lang->isEnabled());
    }

    /** @test */
    public function can_set_and_unset_default()
    {
        $lang = new Lang('pl', 'pl_PL');
        $lang->setIsDefault(true);
        $this->assertTrue($lang->isDefault());
        $lang->setIsDefault(false);
        $this->assertFalse($lang->isDefault());
    }
}

