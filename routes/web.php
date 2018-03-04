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
    // 后台接口定义 -------------------------------------------------------------------------
    $router->group(['prefix' => 'admin','namespace'=>'admin'], function () use ($router) {
        // 登录认证接口组
        $router->group(['prefix' => 'auth'], function () use ($router) {
            // 登录获取token
            $router->post('login', 'AuthController@login');
            // 退出
            $router->post('logout', 'AuthController@logout');
            // 刷新token
            $router->post('refresh', 'AuthController@refresh');
            // 查看个人信息
            $router->post('me', 'AuthController@me');
        });
    });
    // 后台接口定义 end ----------------------------------------------------------------------

    // 前台接口定义 -------------------------------------------------------------------------
    $router->group(['prefix' => 'v1', 'namespace'=>'v1'], function () use ($router) {
        $router->get('test', 'TestController@index');
        $router->post('test', 'TestController@post');
    });
});