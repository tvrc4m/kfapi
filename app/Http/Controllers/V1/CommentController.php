<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
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

    //专家回复列表
    public function getAllComments(Request $request)
    {
        $topicId = $request->input('topic_id');
        $perpage = $request->input('per_page');

        $comments = DB::table('comments')
            ->leftJoin('experts', 'experts.id', '=', 'comments.expert_id')
            ->select('experts.name as expertname','experts.icon','experts.job_id','comments.expert_id','comments.content','comments.created_at')
            ->where('comments.topic_id',$topicId)
            ->paginate($perpage)
            ->toArray();
        //dd($comments);
        $config = require base_path('config/fieldDictionary.php');
        //dd($config['job']);
        $newJob = [];
        foreach($config['job'] as $k=>$v){
            $newJob[$v['job_id']] = $v['name'];
        }
        if($comments['data']){
            foreach ($comments['data'] as $k=>&$v){
                if($v->job_id){
                    $v->job = $newJob[$v->job_id];
                }else{
                    $v->job = '';
                }
            }
        }

        //dd($comments);
        return api_success($comments);
    }

    //首页轮播
    public function getShuffling(Request $request)
    {

        $comments = DB::table('comments')
            ->leftJoin('experts', 'experts.id', '=', 'comments.expert_id')
            ->leftJoin('topics','comments.topic_id','=','topics.id')
            ->select('topics.id as topic_id','experts.name as expertname','experts.icon','experts.job_id','comments.content')
            ->where('comments.top',1)
            ->orderBy('comments.created_at','desc')
            ->limit(4)
            ->get()
            ->toArray();
        //dd($comments);
        $config = require base_path('config/fieldDictionary.php');
        //dd($config['job']);

        $jobs = $config['job'];
        $jobs = array_values($jobs);
        $newJob = [];
        foreach($jobs as $k=>$v){
            $newJob[$v['job_id']] = $v['name'];
        }
        if($comments){
            foreach ($comments as $k=>&$v){
                if($v->job_id){
                    $v->job = $newJob[$v->job_id];
                }else{
                    $v->job = '';
                }
            }
        }
        $data['data'] = $comments;
        //dd($comments);
        return api_success($data);
    }
}