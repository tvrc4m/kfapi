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
        // 新增情感建议匹配关系
        $router->post('/suggest/rule', 'QuestionSuggestController@createRule');
        // 情感建议匹配关系列表
        $router->get('/suggest/rule', 'QuestionSuggestController@getRuleList');
        // 情感建议匹配关系对应问题显示
        $router->get('/suggest/rule/detail', 'QuestionSuggestController@getQuestionList');
        // 修改情感建议匹配关系
        $router->put('/suggest/rule/{id}', 'QuestionSuggestController@editRule');
        // 删除情感建议匹配关系
        $router->delete('/suggest/rule/{id}', 'QuestionSuggestController@deleteRule');
        // 情感建议匹配关系详情
        $router->get('/suggest/rule/{id}', 'QuestionSuggestController@getRuleDetail');

        // 新增建议
        $router->post('/suggest', 'QuestionSuggestController@create');
        // 删除建议
        $router->delete('/suggest/{id}', 'QuestionSuggestController@delete');
        // 建议列表
        $router->get('/suggest', 'QuestionSuggestController@getList');
        // 修改建议
        $router->put('/suggest/{id}', 'QuestionSuggestController@edit');
        // 建议详情
        $router->get('/suggest/{id}', 'QuestionSuggestController@getDetail');

        // 新增问题集
        $router->post('/collection', 'QuestionCollectionController@create');
        // 删除问题集
        $router->delete('/collection', 'QuestionCollectionController@delete');
        // 问题集列表
        $router->get('/collection', 'QuestionCollectionController@getList');
        // 修改问题集
        $router->put('/collection/{id}', 'QuestionCollectionController@edit');
        // 问题集详情
        $router->get('/collection/{id}', 'QuestionCollectionController@getDetail');
        // 所有问题集列表
        $router->get('/allcollection', 'QuestionCollectionController@getAllList');
        // 所有问题列表
        $router->get('/allquestion', 'QuestionCollectionController@getAllQuestionList');

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
        // 获取法规详情
        $router->get('/detail/{id}', 'LawController@getLawDetail');
        // 删除法规
        $router->delete('/{id}', 'LawController@delete');
        // 修改法规条目
        $router->put('/{id}', 'LawController@edit');

        // 获取法规条目列表
        $router->get('/rule', 'LawController@getLawRule');
        // 添加法规条目
        $router->post('/rule', 'LawController@addLawRule');
        // 获取法规条目详情
        $router->get('/rule/detail/{id}', 'LawController@getLawRuleDetail');
        // 删除法规条目
        $router->delete('/rule/{id}', 'LawController@deleteLawRule');
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

    //上传
    $router->group(['prefix' => 'upload'], function () use ($router) {
        // 上传图片
        $router->post('/', 'UploadController@image');
    });

    // 获取专家接口组
    $router->group(['prefix' => 'expert'], function () use ($router) {
        // 获取专家列表
        $router->get('/', 'ExpertController@getAllExpert');
        // 添加专家
        $router->post('/', 'ExpertController@addExpert');
        // 获取专家职业列表
        $router->get('/job', 'ExpertController@getAllJob');
        // 获取专家擅长列表
        $router->get('/goodAt', 'ExpertController@getGoodAt');
        // 获取服务列表
        $router->get('/service', 'ExpertController@getService');
        // 获取认证
        $router->get('/certification', 'ExpertController@getCertification');
        // 删除专家
        $router->delete('/{id}', 'ExpertController@deleteExpert');
        // 查看专家
        $router->get('/{id}', 'ExpertController@getOneExpert');
        // 修改专家
        $router->put('/{id}', 'ExpertController@editExpert');
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

    // 后台帖子组
    $router->group(['prefix' => 'topic'], function () use ($router) {
        // 获取帖子列表
        $router->get('/', 'TopicController@getAllTopics');
        // 点击隐藏
        $router->post('/hide', 'TopicController@changeHide');
        // 点击推荐
        $router->post('/top', 'TopicController@changeTop');
        // 帖子搜索
        $router->get('/search', 'TopicController@searchTopic');
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

        // 查看案例要素对应的关键词列表
        $router->get('factor/keywords', 'CaseController@getFactorList');
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
