<?php
/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class MenuRegisterTest
 *
 * @package    tests\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

namespace tests\Core;


use Gzero\Core\MenuRegister;

class MenuRegisterTest extends \PHPUnit_Framework_TestCase {

    protected $userPanelMenu;

    function setUp()
    {
        parent::setUp();
        $this->exampleData();
    }

    /** @test */
    public function can_add_children()
    {
        $this->userPanelMenu->addChild(['url' => 'child_1', 'title' => 'child_1'], 'url_3');
        $menu = $this->userPanelMenu->getMenu();
        $this->assertEquals(1, count($menu[3]['children']));
    }

    /**
     * Generate example data for this test
     */
    private function exampleData()
    {
        $this->userPanelMenu = new MenuRegister();
        for ($i = 0; $i < 10; $i++) {
            $this->userPanelMenu->addLink('url_' . $i, 'title');
        }
    }

}

