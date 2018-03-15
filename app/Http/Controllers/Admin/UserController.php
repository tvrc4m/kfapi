<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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

    //用户列表
    public function getAllUser()
    {
        $allUser = AdminUser::get()->toArray();

        foreach ($allUser as $k=>$v){
            $userArr[$v['id']] = $v['username'];
        }
        //dd($userArr);
        $user = AdminUser::paginate(20)->toArray();

        if($user['data']){
            foreach($user['data'] as $k=>&$v){
                if($v['create_user_id']!==0){
                    $v['create_user'] = $userArr[$v['create_user_id']];
                }else{
                    $v['create_user'] = '';
                }
                //dd($createUser);
            }
        }

        return api_success($user);
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
