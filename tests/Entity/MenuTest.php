<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class MenuTest
 *
 * @package    tests\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests\Entity;


use Gzero\Entity\Menu;

class MenuTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function is_instantiable()
    {
        $this->assertInstanceOf('Gzero\Entity\Menu', new Menu());
    }

}
