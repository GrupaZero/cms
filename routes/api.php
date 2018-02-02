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

        // ======== Contents ========
        $router->get('contents', 'ContentController@index');
        $router->post('contents', 'ContentController@store');
        $router->patch('contents/{id}', 'ContentController@update');
        $router->delete('contents/{id}', 'ContentController@destroy');
        $router->get('contents-tree/{id?}', 'ContentTreeController@index');
        $router->get('contents/{id}', 'ContentController@show');
        $router->get('contents/{id}/children', 'NestedContentController@index');

        $router->get('contents/{id}/translations', 'ContentTranslationController@index');
        $router->post('contents/{id}/translations', 'ContentTranslationController@store');
        $router->delete('contents/{id}/translations/{translationId}', 'ContentTranslationController@destroy');

        $router->get('contents/{id}/files', 'ContentFileController@index');
        $router->put('contents/{id}/files', 'ContentFileController@sync');

        $router->get('deleted-contents', 'DeletedContentController@index');
        $router->delete('deleted-contents/{id}', 'DeletedContentController@destroy');
        $router->post('deleted-contents/{id}/restore', 'DeletedContentController@restore');

        // ======== Blocks ========
        $router->get('blocks', 'BlockController@index');
        $router->post('blocks', 'BlockController@store');
        $router->patch('blocks/{id}', 'BlockController@update');
        $router->delete('blocks/{id}', 'BlockController@destroy');
        $router->get('blocks/{id}', 'BlockController@show');

        $router->get('blocks/{id}/files', 'BlockFileController@index');
        $router->put('blocks/{id}/files', 'BlockFileController@sync');

        $router->get('blocks/{id}/translations', 'BlockTranslationController@index');
        $router->post('blocks/{id}/translations', 'BlockTranslationController@store');
        $router->delete('blocks/{id}/translations/{translationId}', 'BlockTranslationController@destroy');
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
        $router->get('public-contents', 'PublicContentController@index');
    }
);
