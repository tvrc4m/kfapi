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

    /**
     * 新增专家回复(循环1000)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addComment(Request $request)
    {

        $this->validate($request, [
            'topic_id' => 'required|numeric',
            'content' => 'required|max:1000',
        ],[
            'topic_id.required' => '问题ID不能为空',
            'topic_id.numeric' => '问题ID不合法',
            'content.required' => '回复不能为空',
            'content.max' => '回复不能超过1000字符',
        ]);
        $topicId = $request->input('topic_id');
        //dd($topicId);
        $expert = Auth::guard('expert')->user()->toArray();
        if(!$expert){
            return api_error('请登录');
        }

        $data = array(
            'topic_id'=>$request->input('topic_id'),
            'content'=>$request->input('content'),
            'expert_id'=>$expert['id'],
        );

        // 开启事务
        DB::beginTransaction();
        $comment = Comments::create($data);
        $comments = DB::update('update bu_topics set comments=comments+1 where id = ?', [$topicId]);
        if(!$comments || !$comment){
            DB::rollBack();
            return api_error();
        }
        DB::commit();
        return api_success();
    }
}
