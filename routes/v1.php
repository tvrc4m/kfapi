<?php
/**
 * 前台接口定义
 * User: dawn
 * Date: 2018/3/5
 * Time: 下午1:10
 */

$router->get('test', 'TestController@index');
$router->post('test', 'TestController@post');