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
//dd($expertId);
        //专家名称、上次登录时间
        $expert = DB::table('experts')
            ->select('name','last_login_time')
            ->where('id',$expertId)
            ->first();
        //dd($expert);
        //已回答问题数量、
        $num = DB::select('select count(DISTINCT(topic_id)) as answered_question_num from bu_comments where expert_id = ?', [$expertId]);
        //dd($num);
        $answered_question_num = $num[0]->answered_question_num ??0;
        //dd($answered_question_num);
        //所有人回答问题数量、排名
        $userNum = DB::select('select expert_id,count(DISTINCT(topic_id)) as user_num from bu_comments group by expert_id');
        //dd($userNum);
        $expertSort = 0;
        if($userNum){
            foreach ($userNum as $k=>$v){
                $userArr[] = $v->user_num;
            }
            //dd($userNum);
            array_multisort($userArr,SORT_DESC,$userNum);
            //dd($userNum);
            foreach ($userNum as $k=>$v){
                $sort = $k+1;
                //$newNum[$v->expert_id]['sort'] = $sort;
                if($expertId==$v->expert_id){
                    $expertSort = $sort;
                }
            }
        }

        //dd($expertSort);
        //未回答问题数量
        //问题总数量、
        $totalNum = DB::select('select count(DISTINCT(topic_id)) as total_num,count(DISTINCT(user_id)) as ask_people_num from bu_invitations where expert_id = ?', [$expertId]);
        //dd($totalNum);
        //dd($totalNum);
        $unanswered_question_num = 0;
        if($totalNum[0]->total_num){
            $unanswered_question_num = intval($totalNum[0]->total_num) - $answered_question_num;
        }
        $ask_people_num = 0;
        if($totalNum[0]->ask_people_num){
            $ask_people_num = $totalNum[0]->ask_people_num;
        }

        $data = array(
            'name'=>$expert->name,
            'last_login_time'=>$expert->last_login_time,
            'answered_question_num'=>$answered_question_num,
            'sort'=>$expertSort,
            'unanswered_question_num'=>$unanswered_question_num,
            'ask_people_num'=>$ask_people_num,
        );
        return api_success($data);
    }
}