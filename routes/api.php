<?php

// Admin API
use Barryvdh\Cors\HandleCors;
use Gzero\Core\Http\Middleware\AdminAccess;

Route::group(
    [
        'domain'     => 'api.' . config('gzero.domain'),
        'prefix'     => 'v1',
        'namespace'  => 'Gzero\Cms\Http\Controllers\Api',
        'middleware' => [HandleCors::class, 'auth:api', AdminAccess::class]
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */
        $router->get('contents', 'ContentController@index');
        $router->post('contents', 'ContentController@store');
        $router->patch('contents/{id}', 'ContentController@update');
        $router->delete('contents/{id}', 'ContentController@destroy');
        $router->get('contents/{id}', 'ContentController@show');
        $router->get('contents/{id}/children', 'NestedContentController@index');

        $router->get('deleted-contents', 'DeletedContentController@index');
    }
);

// Public API
Route::group(
    [
        'domain'     => 'api.' . config('gzero.domain'),
        'prefix'     => 'v1',
        'namespace'  => 'Gzero\Cms\Http\Controllers\Api',
        'middleware' => [HandleCors::class]
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */
    }
);
