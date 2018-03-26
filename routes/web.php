<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    // 后台接口定义
    $router->group(['prefix' => 'admin','namespace'=>'Admin'], function () use ($router) {
        require __DIR__.'/admin.php';
    });

    // 前台接口定义
    $router->group(['prefix' => 'v1', 'namespace'=>'V1', 'middleware' => 'auth:api'], function () use ($router) {
        require __DIR__.'/v1.php';
    });

    // 前后端公共接口定义
    $router->group(['prefix' => 'common', 'namespace'=>'Common'], function () use ($router) {
        require __DIR__.'/common.php';
    });

    // 专家后端接口定义
    $router->group(['prefix' => 'expert', 'namespace'=>'Expert'], function () use ($router) {
        require __DIR__.'/expert.php';
    });
});