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
//$router->group(['middleware' => 'auth:admin'], function () use ($router) {
$router->group([], function () use ($router) {
    // 问题
    $router->group(['prefix' => 'question'], function () use ($router) {
        // 新增问题集
        $router->post('/collection', 'QuestionCollectionController@create');
        // 修改问题集
        $router->put('/collection/{id}', 'QuestionCollectionController@edit');
        // 删除问题集
        $router->delete('/collection', 'QuestionCollectionController@delete');
        // 问题集列表
        $router->get('/collection', 'QuestionCollectionController@getList');
        // 问题集详情
        $router->get('/collection/{id}', 'QuestionCollectionController@getDetail');

        // 新增问题
        $router->post('/', 'QuestionController@createQuestion');
        // 问题列表
        $router->get('/', 'QuestionController@getAllQuestion');
        // 修改问题
        $router->put('/{id}', 'QuestionController@editQuestion');
        // 删除问题
        $router->delete('/{id}', 'QuestionController@deleteQuestion');
        // 问题详情
        $router->get('/{id}', 'QuestionController@getOneQuestion');
    });

    //法规接口组
    $router->group(['prefix' => 'law'], function () use ($router) {
        // 添加法规
        $router->post('/', 'LawController@addLaw');
        // 获取法规列表
        $router->get('/', 'LawController@getLaw');
        // 删除法规
        $router->delete('/{id}', 'LawController@delete');
        // 获取法规详情
        $router->get('/detail/{id}', 'LawController@getLawDetail');
        // 修改法规条目
        $router->put('/{id}', 'LawController@edit');

        // 获取法规条目列表
        $router->get('/rule', 'LawController@getLawRule');
        // 添加法规条目
        $router->post('/rule', 'LawController@addLawRule');
        // 删除法规条目
        $router->delete('/rule/{id}', 'LawController@deleteLawRule');
        // 获取法规条目详情
        $router->get('/rule/detail/{id}', 'LawController@getLawRuleDetail');
        // 修改法规条目
        $router->put('/rule/{id}', 'LawController@editLawRule');
    });

    //关键词
    $router->group(['prefix' => 'keyword'], function () use ($router) {
        // 要素列表
        $router->get('/getFactorList', 'KeywordController@getFactorList');
        // 某个要素下的关键词列表
        $router->get('/getKeywordList/{id}', 'KeywordController@getKeywordList');
    });

    // 获取专家接口组
    $router->group(['prefix' => 'expert'], function () use ($router) {
        // 获取专家列表
        $router->get('/', 'ExpertController@getAllExpert');
        // 添加专家
        $router->post('/', 'ExpertController@addExpert');
        // 删除专家
        $router->delete('/{id}', 'ExpertController@deleteExpert');
        // 查看专家
        $router->get('/{id}', 'ExpertController@getOneExpert');
        // 修改专家
        $router->put('/{id}', 'ExpertController@editExpert');
        // 获取专家职业列表
        $router->get('/job', 'ExpertController@getAllJob');
    });

    // 用户接口组
    $router->group(['prefix' => 'user'], function () use ($router) {
        // 获取用户列表
        $router->get('/', 'UserController@getAllUser');
        // 添加用户
        $router->post('/', 'UserController@addUser');
        // 删除用户
        $router->delete('/{id}', 'UserController@deleteUser');
        // 查看用户
        $router->get('/{id}', 'UserController@getOneUser');
        // 修改用户
        $router->put('/{id}', 'UserController@editUser');
    });

    // 案例库
    $router->group(['prefix' => 'case'], function () use ($router) {
        // 搜索关键词
        $router->post('searchKeyword', 'CaseController@searchKeyword');
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
        // 修改案例要素
        $router->put('factor/{id}', 'CaseController@editFactor');

        // 获得案例分类
        $router->get('/cate', 'CaseController@getAllCate');
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
