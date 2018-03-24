<?php

namespace App\Http\Controllers\expert;
use App\Http\Controllers\Controller;

use App\Models\Topics;
use App\Models\Comments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
//        $expertId = Auth::guard('expert')->user();
        $expertId = 30;

        $topics = DB::table('topics')
            ->leftJoin('invitations', 'invitations.topic_id', '=', 'topics.id')
            ->leftJoin('users', 'users.id', '=', 'topics.user_id')
            ->select('topics.id','topics.content','topics.created_at','users.user_name')
            ->where('invitations.expert_id',$expertId)
            ->orderBy('topics.created_at','desc')
            ->paginate(20)
            ->toArray();
        //dd($topics);
        return api_success($topics);
    }

    /**
     * 问题详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneTopic($id)
    {
        //dd($data);
        $topic = DB::table('topics')
            ->leftJoin('users', 'users.id', '=', 'topics.user_id')
            ->leftJoin('user_question_report', 'user_question_report.user_id', '=', 'topics.user_id')
            ->select('topics.content','topics.created_at','users.user_name','users.province_id','users.city_id','user_question_report.id','user_question_report.understand')
            ->where('topics.id',$id)
            ->first();
        //dd($topic);
        if($topic){
            $city = DB::select('select p.name as provincename,c.name as cityname from bu_provinces as p left join bu_citys as c on c.provinceid= p.id where c.provinceid =? and c.cityid=?',[$topic->province_id,$topic->city_id]);
            //dd($city);
            $topic->area = $city ? $city[0]->provincename.$city[0]->cityname : '';
            $topic->opinion_id = $topic->id;
            $topic->opinion_content = mb_substr($topic->understand,0,40);
            unset($topic->id,$topic->understand,$topic->province_id,$topic->city_id);
            return api_success($topic);
        }
        return api_error();
    }

    /**
     * 新增专家回复
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
        $expert = Auth::guard('expert')->user()->toArray();
        //dd($expert);
        $data = array(
            'topic_id'=>$request->input('topic'),
            'content'=>$request->input('content'),
            'expert_id'=>$expert['id'],
        );
        $comment = Comments::create($request->all());
        if ($comment) {
            return api_success($comment);
        }
        return api_error();
    }
}
