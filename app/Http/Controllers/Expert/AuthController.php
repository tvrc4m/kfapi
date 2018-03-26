<?php
namespace App\Http\Controllers\Expert;

use App\Models\Experts;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:expert', ['except' => ['login']]);
    }

    /**
     * 登录下发token
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = [
            'account' => $request->input('account'),
            'password' => $request->input('password'),
            'stat' => 1,
        ];
//        $pwd = Hash::make($credentials['password']);dd($pwd);
//        print_sql();
//        $aaa = AdminExpert::where('account', $credentials['account'])->first();
//        dd($aaa->password);
//        $res = Hash::check($credentials['account'], $aaa->password);
//        dd($res);

//dd($credentials);
        if (! $token = Auth::guard("expert")->setTTL(60)->attempt($credentials)) {
            //dd($token);
            return api_error('用户名或密码错误');
        }

        return $this->respondWithToken($token);
    }

    /**
     * 获得用户信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return api_success(Auth::guard("expert")->user());
    }

    /**
     * 退出登录
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard("expert")->logout();
        return api_success();
    }

    /**
     * 刷新token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::guard("expert")->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return api_success([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard("expert")->factory()->getTTL() * 60
        ]);
    }

    /**
     * 欢迎页
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function welcome()
    {
        $expertId = Auth::guard("expert")->user()['id'];
//sdd($expertId);
        //专家名称、上次登录时间
        $expert = DB::table('experts')
            ->select('name','last_login_time')
            ->where('id',$expertId)
            ->first();
        //dd($expert);
        //已回答问题数量、
        $num = DB::select('select count(topic_id) as answered_question_num from bu_comments where expert_id = ?', [$expertId]);
        //所有人回答问题数量、排名
        $userNum = DB::select('select expert_id,count(topic_id) as user_num from bu_comments group by expert_id');
        foreach ($userNum as $k=>$v){
            $userArr[] = $v->user_num;
        }
        //dd($userArr);
        array_multisort($userArr,SORT_DESC,$userNum);
        //dd($userNum);
        foreach ($userNum as $k=>$v){
            $sort = $k+1;
            $newNum[$v->expert_id]['sort'] = $sort;
        }
        $expertSort = $newNum[$expertId]['sort'];
        //dd($expertSort);
        //未回答问题数量
        //问题总数量、
        $totalNum = DB::select('select count(topic_id) as total_num from bu_invitations where expert_id = ?', [$expertId]);
        dd($totalNum);
    }
}