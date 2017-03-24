<?php namespace unit;

use Gzero\Core\Menu\Link;
use Gzero\Core\Menu\Register;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ValidatorTest
 *
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2017, Adrian Skierniewski
 */
class MenuRegisterTest extends \Codeception\Test\Unit {

    /**
     * @var Register
     */
    protected $register;

    protected function _before()
    {
        $this->register = new Register();
    }


    /**
     * @test
     */
    public function it_adds_simple_links()
    {
        $this->register->add(new Link('url-1', 'title for url 1'));
        $this->register->add(new Link('url-2', 'title for url 2', 99));

        $menu = $this->register->getMenu();

        $this->assertEquals('url-1', $menu->get(0)->url);
        $this->assertEquals('title for url 1', $menu->get(0)->title);
        $this->assertEquals(0, $menu->get(0)->weight);
        $this->assertEquals('url-2', $menu->get(1)->url);
        $this->assertEquals('title for url 2', $menu->get(1)->title);
        $this->assertEquals(99, $menu->get(1)->weight);
    }

    /**
     * @test
     */
    public function it_sorts_by_weight()
    {
        $this->register->add(new Link('url-1', 'title for url 1', 1337));
        $this->register->add(new Link('url-2', 'title for url 2', 99));

        $menu = $this->register->getMenu();

        $this->assertEquals('url-2', $menu->get(0)->url);
        $this->assertEquals('title for url 2', $menu->get(0)->title);
        $this->assertEquals(99, $menu->get(0)->weight);
        $this->assertEquals('url-1', $menu->get(1)->url);
        $this->assertEquals('title for url 1', $menu->get(1)->title);
        $this->assertEquals(1337, $menu->get(1)->weight);
    }

    /**
     * @test
     */
    public function it_allows_to_add_child_link()
    {
        $this->register->add(new Link('url-1', 'title for url 1', 1337));
        $this->register->add(new Link('url-2', 'title for url 2', 99));
        $this->register->addChild('url-2', new Link('child-2-1', 'child 2-1 title', 99));
        $this->register->addChild('url-2', new Link('child-2-2', 'child 2-2 title'));

        $menu = $this->register->getMenu();

        $this->assertEquals('url-2', $menu->get(0)->url);
        $this->assertEquals('title for url 2', $menu->get(0)->title);
        $this->assertEquals(99, $menu->get(0)->weight);
        // Children
        $this->assertCount(2, $menu->get(0)->children);
        $this->assertEquals('child-2-2', $menu->get(0)->children->get(0)->url);
        $this->assertEquals('child-2-1', $menu->get(0)->children->get(1)->url);
    }
}
