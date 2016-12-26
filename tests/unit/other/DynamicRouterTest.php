<?php namespace unit;

use Gzero\Core\DynamicRouter;
use Gzero\Core\Events\ContentRouteMatched;
use Gzero\Entity\Content;
use Gzero\Entity\Lang;
use Illuminate\Http\Request;
use Mockery as m;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ValidatorTest
 *
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class DynamicRouterTest extends \Codeception\Test\Unit {

    /**
     * @var \Mockery\MockInterface
     */
    protected $repositoryMock;

    /**
     * @var \Mockery\MockInterface
     */
    protected $eventDispatcherMock;

    /**
     * @var DynamicRouter|\Mockery\MockInterface
     */
    protected $router;

    protected function _before()
    {
        $this->eventDispatcherMock = m::mock('Illuminate\Events\Dispatcher');
        $this->repositoryMock      = m::mock('Gzero\Repository\ContentRepository');
        $this->router              = m::mock('Gzero\Core\DynamicRouter', [$this->repositoryMock, $this->eventDispatcherMock])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
    }

    protected function _after()
    {
        m::close();
    }

    /**
     * @test
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function it_throws_not_found_exceptions()
    {
        $this->repositoryMock
            ->shouldReceive('getByUrl')
            ->andReturn(null)
            ->once()
            ->getMock();
        $this->eventDispatcherMock
            ->shouldReceive('fire')
            ->never()
            ->getMock();
        $this->router->handleRequest('test-url', new Lang(), new Request());
    }

    /**
     * @test
     */
    public function it_fires_content_route_match_event()
    {
        $request = new Request();
        $content = new Content(['is_active' => true, 'type' => 'category']);
        $this->repositoryMock
            ->shouldReceive('getByUrl')
            ->andReturn($content)
            ->once()
            ->getMock();
        $this->eventDispatcherMock
            ->shouldReceive('fire')
            ->with(
                m::on(
                    function ($event) use ($content, $request) {
                        $this->assertEquals(get_class($event), ContentRouteMatched::class);
                        $this->assertSame($event->content, $content);
                        $this->assertSame($event->request, $request);
                        return true;
                    }
                )
            )
            ->once()
            ->getMock();
        $this->router->shouldReceive('resolveType')
            ->andReturn(
                new class {
                    function load()
                    {
                        return $this;
                    }

                    function render()
                    {
                        return 'rendered';
                    }
                }
            );
        $result = $this->router->handleRequest('test-url', new Lang(), $request);
        $this->assertEquals($result, 'rendered');
    }

}
