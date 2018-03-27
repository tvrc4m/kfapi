<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use App\Models\Comments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    //回复列表
    public function getAllComments(Request $request)
    {
        $type = $request->input('type');
        $hide_comment = $request->input('hide_comment');
        $where = [];
        if (!empty($type)) {
            $where['type'] = $type;
        }
        if ($hide_comment) {
            $where['comments.is_hide'] = 2;
        }

        $comments = DB::table('comments')
            ->leftJoin('experts', 'comments.expert_id', '=', 'experts.id')
            ->select('comments.id','comments.content','comments.topic_id','comments.created_at','comments.is_hide','comments.top','comments.expert_id','experts.name')
            ->where($where)
            ->orderBy('comments.created_at','desc')
            ->paginate(20)
            ->toArray();
        //dd($comments);
        return api_success($comments);
    }



    /**
     * 隐藏问题/取消隐藏
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeHide(Request $request)
    {
        $cid = $request->input('comment_id');
        //dd($data);
        $hide = $request->input('hide_stat');
        $data = ['is_hide'=>$hide];
        $comments = Comments::where('id', $cid)->firstOrFail();
        if ($comments->update($data)) {
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
        $cid = $request->input('comment_id');
        //dd($data);
        $top = $request->input('top_stat');
        $data = ['top'=>$top];
        $comments = Comments::where('id', $cid)->firstOrFail();
        if ($comments->update($data)) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 问题搜索
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchComment(Request $request)
    {
        $content = $request->input('content');
        //dd($data);
        $comments = Comments::where('content', 'like','%'.$content.'%')->paginate(20);
        if ($comments) {
            return api_success($comments);
        }
        return api_error();
    }
}
