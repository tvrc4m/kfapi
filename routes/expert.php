<?php
/**
 * 专家后台接口定义
 * User: dawn
 * Date: 2018/3/5
 * Time: 下午1:10
 */

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

// 需要登录才能使用的api组 使用了auth验证中间件
//$router->group(['middleware' => 'auth:expert'], function () use ($router) {
$router->group([], function () use ($router) {
    //
    $router->group(['prefix' => 'topic'], function () use ($router) {
        //用户提问列表
        $router->get('/', 'TopicController@getAllTopics');
        //问题详情
        $router->get('/{id}', 'TopicController@getOneTopic');
        //专家提交回复
        $router->post('/', 'TopicController@addComment');
    });
});
