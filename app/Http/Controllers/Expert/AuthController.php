<?php
namespace App\Http\Controllers\Expert;

use App\Models\AdminExpert;
use App\Models\Experts;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            dd($token);
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
}