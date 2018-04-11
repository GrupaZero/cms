<?php namespace unit;

use Codeception\Test\Unit;
use Gzero\Cms\Menu\Link;
use Gzero\Cms\Menu\Register;

class MenuRegisterTest extends Unit {

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
    public function itAddsSimpleLinks()
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
    public function itSortsByWeight()
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
    public function itAllowsToAddChildLink()
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
