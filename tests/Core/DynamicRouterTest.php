<?php namespace tests\Core;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class DynamicRouterTest
 *
 * @package    tests\Core
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */

use Gzero\Core\DynamicRouter;
use Gzero\Entity\Content;
use Gzero\Entity\ContentType;
use Gzero\Entity\Lang;
use Mockery as M;

class DynamicRouterTest extends \Doctrine2TestCase {

    protected $app = NULL;
    protected $router = NULL;
    protected $contentRepo = NULL;

    public function tearDown()
    {
        M::close();
    }

    public function setUp()
    {
        $this->app         = M::mock('Illuminate\Foundation\Application');
        $this->contentRepo = M::mock('Gzero\Repository\ContentRepository');
        $this->router      = new DynamicRouter($this->app, $this->contentRepo);

        parent::setUp();
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function handle_request_not_found()
    {
        $this->contentRepo->shouldReceive('getByUrl')->andReturnNull();
        $this->router->handleRequest('dummy-url', new Lang('pl', 'pl_PL'));
    }

    /**
     * @test
     */
    public function resolve_type()
    {
        $content = new Content(new ContentType('product'));
        $content->setActive(TRUE);
        $handler = M::mock('Gzero\Core\Handler\Content\ContentTypeHandler'); //DummyHandler for build() method
        $handler->shouldReceive('load->render');
        $this->contentRepo->shouldReceive('getByUrl')->andReturn($content);
        $this->app->shouldReceive('make')->with('content_type:product')->andReturn($handler);
        $this->router->handleRequest('dummy-url', new Lang('pl', 'pl_PL'));
    }
} 
