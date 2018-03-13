<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 问题控制器
 * @package App\Http\Controllers\Admin
 */
class QuestionController extends Controller
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
     * 新增问题
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createQuestion(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
            'title' => 'required|max:255',
            'bgimage' => 'required',
            'type' => 'required',
            'options' => 'required|array',
        ],[
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'question_collection_id.required' => '问题集id不能为空',
            'question_collection_id.numeric' => '问题集id必须是数字',
            'bgimage.required' => '背景图片不能为空',
            'type.required' => '不能为空',
            'options.required' => '不能为空',
        ]);
        
        return api_success();
    }
}
