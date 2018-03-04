<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 测试展示
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return api_success();
    }

    /**
     * 测试展示数据验证
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'age' => 'required|numeric|between:1,150',
        ],[
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过50个字符',
            'age.required' => '年龄不能为空',
            'age.numeric' => '年龄必须是数字',
            'age.between' => '年龄必须大于1小于150',
        ]);

        return api_success();
    }
}
