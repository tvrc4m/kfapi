<?php
/**
 * 前台接口定义
 * User: dawn
 * Date: 2018/3/5
 * Time: 下午1:10
 */

$router->get('test', 'TestController@index');
$router->post('test', 'TestController@post');

// 问题相关
$router->group(['prefix' => 'question'], function () use ($router) {
    // 生成报告书
    $router->post('/report', 'QuestionController@makeReport');
    // 开始答题
    $router->get('/begin', 'QuestionController@begin');
    // 获得问题
    $router->get('/', 'QuestionController@question');
    // 提交问题
    $router->post('/', 'QuestionController@answer');
});

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

// 获取用户问题组
$router->group(['prefix' => 'topic'], function () use ($router) {
    // 获取大家在测列表
    $router->get('/', 'TopicController@getAllTopic');
    // 用户提交问题
    $router->post('/', 'TopicController@addTopic');
    // 首页轮播图
    $router->get('/shuffling', 'TopicController@getShuffling');
    // 获取问答详情页
    $router->get('/{id}', 'TopicController@getOneTopic');

});
// 用户邀请
$router->group(['prefix' => 'invitation'], function () use ($router) {
    // 用户邀请专家
    $router->post('/', 'InvitationController@addInvitation');
});

// 确认下单
$router->group(['prefix' => 'order'], function () use ($router) {

    // 提交订单
    $router->post('/', 'OrderController@addOrder');
    // 查看下单信息
    $router->get('/', 'OrderController@getOrder');
});

// 评测记录
$router->group(['prefix' => 'record'], function () use ($router) {

    // 我的提问列表
    $router->get('/', 'RecordController@getAllRecord');
    // 删除我的提问记录
    $router->delete('/', 'RecordController@deleteRecord');
    // 我的评测列表
    $router->get('/opinion', 'RecordController@getAllOpinion');
    // 删除我的评测记录
    $router->delete('/opinion', 'RecordController@deleteOpinion');
    // 查看我的评测记录详情
    $router->get('/opinion/{id}', 'RecordController@getOneOpinion');
});