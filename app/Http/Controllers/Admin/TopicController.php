<?php

namespace App\Http\Controllers\admin;
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

    //帖子列表
    public function getAllTopics(Request $request)
    {
        $cate = $request->input('cate');
        $sort = $request->input('sort');
        $hide_question = $request->input('hide_question');
        $hide_comment = $request->input('hide_comment');

        $where = [];
        if (!empty($cate)) {
            $where['cate'] = $cate;
        }

        if (!empty($sort) && $sort=='comment') {
            $sortfield = 'comments.created_at';
        }else{
            $sortfield = 'topics.created_at';
        }

        if ($hide_question) {
            $where['topics.is_hide'] = $hide_question;
        }

        if ($hide_comment) {
            $where['comments.is_hide'] = $hide_comment;
        }

        $topics = DB::table('topics')
            ->leftJoin('comments', 'comments.topic_id', '=', 'topics.id')
            ->select('topics.id','topics.content','topics.comments','topics.created_at as question_time','topics.user_id')
            ->where($where)
            ->groupBy('topics.id')
            ->orderBy($sortfield,'desc')
            ->paginate(20)
            ->toArray();
        //dd($topics);
        $users = DB::table('users')
            ->select('users.user_name','users.id')
            ->get()
            ->toArray();
        //dd($users);
        foreach($users as $k=>$v){
            $userArr[$v->id] = $v->user_name;
        }
        //dd($userArr);
        //dd($topics['data']);
        if($topics['data']){
            foreach($topics['data'] as $k=>&$v){
//                dd($v);
//                dd($v['user_id']);
                //dd($userArr[$v->user_id]);
                $v->user_name = $userArr[$v->user_id];
            }
        }
        //dd($topics);
        return api_success($topics);
    }

    /**
     * 查看用户
     * @param $id
     */
    public function getOneUser($id)
    {
        $data = AdminUser::where('id', $id)->firstOrFail();
        return api_success($data);
    }

    /**
     * 新增用户
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addUser(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|max:255',
            'password' => 'required|max:255',
            'email' => 'required|email'
        ],[
            'username.required' => '用户名不能为空',
            'username.max' => '用户名不能超过255个字符',
            'password.required' => '密码不能为空',
            'password.max' => '密码不能超过255个字符',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
        ]);

        $data = $request->all();
        $userinfo = Auth::guard("admin")->user()->toArray();
        dd($userinfo);
        $data['create_user_id'] = $userinfo['id'];
        $data['password'] = Hash::make($request->input('password'));
        //dd($data);
        $createUser = AdminUser::create($data);

        if(!$createUser){
            return api_error();
        }
        return api_success();
    }

    /**
     * 删除用户
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser($id)
    {
        if (AdminUser::destroy(intval($id))) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 修改用户
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editUser($id, Request $request)
    {
        $this->validate($request, [
            'username' => 'required|max:255',
            'password' => 'required|max:255',
            'email' => 'required|email'
        ],[
            'username.required' => '用户名不能为空',
            'username.max' => '用户名不能超过255个字符',
            'password.required' => '密码不能为空',
            'password.max' => '密码不能超过255个字符',
            'email.required' => '邮箱不能为空',
            'email.email' => '邮箱格式不正确',
        ]);

        $userinfo = Auth::guard("admin")->user()->toArray();
        $createUser = AdminUser::where('id',$id)->first();
        //dd($user);
        $data = $request->all();
        $data['password'] = Hash::make($request->input('password'));
        $data['create_user_id'] = $userinfo['id'];

        $res = $createUser->update($data);
        if(!$res){
            return api_error();
        }
        return api_success();
    }

}
