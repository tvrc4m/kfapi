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
            ->where('topic_id',$topicId)
            ->paginate($perpage)
            ->toArray();
        //dd($comments);
        $config = require APP_PATH . 'config/fieldDictionary.php';
        if($comments['data']){
            foreach ($comments['data'] as $k=>&$v){
                $v->job = $config['job'][$v->job_id];
            }
        }

        //dd($comments);
        return api_success($comments);
    }

}