<?php
/**
 * 后台接口定义
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
$router->group(['middleware' => 'auth:admin'], function () use ($router) {
    // 获取专家接口组
    $router->group(['prefix' => 'expert'], function () use ($router) {
        // 获取专家列表
        $router->get('index', 'ExpertController@index');
        // 添加专家
        $router->post('add', 'ExpertController@add');
        // 删除专家
        $router->delete('delete', 'ExpertController@delete');
        // 专家编辑页
        $router->post('edit', 'ExpertController@edit');
    });
    // 案例库
    $router->group(['prefix' => 'case'], function () use ($router) {
        // 新增关键词
        $router->post('keyword', 'CaseController@createKeyword');
        // 保存案例的关键词
        $router->put('keyword', 'CaseController@editKeyword');
        // 查看案例关键词
        $router->get('keyword', 'CaseController@getAllKeyword');

        // 查看案例要素
        $router->get('factor', 'CaseController@getAllFactor');
        // 新增案例要素
        $router->post('factor', 'CaseController@createFactor');
        // 查看案例要素
        $router->put('factor/{id}', 'CaseController@editFactor');

        // 创建案例
        $router->post('/', 'CaseController@createCase');
        // 删除案例
        $router->delete('/{id}', 'CaseController@deleteCase');
        // 修改案例
        $router->put('/{id}', 'CaseController@editCase');
        // 案例列表
        $router->get('/', 'CaseController@getAllCase');
        // 查看案例
        $router->get('/{id}', 'CaseController@getOneCase');
    });
});
