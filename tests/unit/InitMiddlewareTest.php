<?php namespace unit;

require __DIR__ . '/../../src/Gzero/Core/helpers.php';

use Illuminate\Http\Request;
use \Mockery as m;
use Gzero\Core\Middleware\Init;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class InitMiddlewareTest
 *
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2016, Adrian Skierniewski
 */
class InitMiddlewareTest extends \Codeception\Test\Unit {

    /**
     * @var Init
     */
    protected $middleware;


    protected function _before()
    {
        $this->middleware = new Init();
    }

    protected function _after()
    {
        m::close();
    }

    /**
     * @test
     */
    public function it_should_change_params_to_snake_case()
    {
        $request = new Request();
        $request->replace(['isActive' => 1, 'langCode' => 'en']);
        $this->middleware->handle(
            $request,
            function () {
            }
        );
        $this->assertEquals($request->all(), ['is_active' => 1, 'lang_code' => 'en']);
    }

    /**
     * @test
     */
    public function it_should_change_nested_params_to_snake_case()
    {
        $request = new Request();
        $request->replace(
            [
                'isActive'     => 1,
                'translations' => [
                    ['langCode' => 'en'],
                    ['langCode' => 'pl'],
                    [
                        'langCode'     => 'de',
                        'customFields' => ['lol' => 1]
                    ]
                ]
            ]
        );
        $this->middleware->handle(
            $request,
            function () {
            }
        );
        $this->assertEquals(
            $request->all(),
            [
                'is_active'    => 1,
                'translations' => [
                    ['lang_code' => 'en'],
                    ['lang_code' => 'pl'],
                    [
                        'lang_code'     => 'de',
                        'custom_fields' => ['lol' => 1]
                    ]
                ]
            ]
        );
    }

}
