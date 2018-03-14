<?php
/**
 * 前台接口定义
 * User: dawn
 * Date: 2018/3/5
 * Time: 下午1:10
 */

$router->get('test', 'TestController@index');
$router->post('test', 'TestController@post');

// 获取专家接口组
$router->group(['prefix' => 'expert'], function () use ($router) {
    // 获取专家列表
    $router->get('/', 'ExpertController@getAllExpert');
    // 获取专家详情
    $router->get('/{id}', 'ExpertController@getOneExpert');
});
// 获取专家回复组
$router->group(['prefix' => 'comment'], function () use ($router) {
    // 获取专家回复列表
    $router->get('/', 'CommentController@getAllComments');
});
