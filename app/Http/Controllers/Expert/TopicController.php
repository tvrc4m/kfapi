<?php

namespace App\Http\Controllers\expert;
use App\Http\Controllers\Controller;

use App\Models\Topics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TopicController extends Controller
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

    //用户问题列表
    public function getAllTopics(Request $request)
    {
        $expertId = Auth::guard('expert')->user()->toArray();
        $expertId = $request->input('expert_id');

        $topics = DB::table('topics')
            ->leftJoin('users', 'users.id', '=', 'topics.user_id')
            ->select('topics.id','topics.content','topics.created_at','users.user_name')
            ->where('')
            ->paginate(20)
            ->toArray();
        dd($topics);
        $users = DB::table('users')
            ->select('users.user_name','users.id')
            ->get()
            ->toArray();
        //dd($users);
        foreach($users as $k=>$v){
            $userArr[$v->id] = $v->user_name;
        }
        //dd($userArr);
        if($topics['data']){
            foreach($topics['data'] as $k=>&$v){
                $v->user_name = $userArr[$v->user_id];
            }
        }
        //dd($topics);
        return api_success($topics);
    }



    /**
     * 隐藏问题/取消隐藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeHide(Request $request)
    {
        $qid = $request->input('topic_id');
        //dd($data);
        $hide = $request->input('hide_stat');
        $data = ['is_hide'=>$hide];
        $question = Topics::where('id', $qid)->firstOrFail();
        if ($question->update($data)) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 隐藏问题/取消隐藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeTop(Request $request)
    {
        $qid = $request->input('topic_id');
        //dd($data);
        $top = $request->input('top_stat');
        $data = ['top'=>$top];
        $question = Topics::where('id', $qid)->firstOrFail();
        if ($question->update($data)) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 问题搜索
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchTopic(Request $request)
    {
        $content = $request->input('content');
        //dd($data);
        $topic = Topics::where('content', 'like','%'.$content.'%')->paginate(20);
        if ($topic) {
            return api_success($topic);
        }
        return api_error();
    }
}
