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
    // 获取专家列表
    $router->get('/begin', 'QuestionController@begin');
    // 获取专家详情
    $router->get('/{id}', 'QuestionController@begin');
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
    // 获取问答详情页
    $router->get('/{id}', 'TopicController@getOneTopic');
    // 用户提交问题
    $router->post('/', 'TopicController@addTopic');
});
// 用户邀请
$router->group(['prefix' => 'invitation'], function () use ($router) {
    // 用户邀请专家
    $router->post('/', 'InvitationController@addInvitation');
});

