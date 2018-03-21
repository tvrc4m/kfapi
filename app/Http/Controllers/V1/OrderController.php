<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
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

    //提交订单
    public function addOrder(Request $request)
    {
        $this->validate($request, [
            'service_id' => 'required|numeric',
            'expert_id' => 'required|numeric',
            'device' => 'required|max:255',
            'remark' => 'required|max:255',
            'user_name' => 'required|max:60',
            'phone' => 'required|max:11',

        ],[
            'service_id.required' => '服务id不能为空',
            'service_id.numeric' => '服务id不合法',
            'expert_id.required' => '专家id不能为空',
            'expert_id.numeric' => '专家id不合法',
            'device.required' => '用户设备号不能为空',
            'device.max' => '用户设备号不能超过255个字符',
            'remark.required' => '备注不能为空',
            'remark.max' => '备注不能超过255个字符',
            'user_name.required' => '用户名不能为空',
            'user_name.max' => '用户名不超过60个字符',
            'phone.required' => '用户电话不能为空',
            'phone.max' => '用户电话不能超过11位',
        ]);

        $data=$request->except('user_name','phone');
        //dd($data);
        // 开启事务
        DB::beginTransaction();

        $order = Order::create($data);
        $userinfo = $request->only('device','user_name','phone');

        $user = User::create($userinfo);
        if(!$order && !$user){
            DB::rollBack();
            return api_error();
        }
        DB::commit();

        return api_success();
    }


    /**
     * 订单信息                                                                                                                                                                                                                                                            详情
     * @param $id
     */
    public function getOrder(Request $request)
    {
        //print_sql();
        $expertid = $request->input('expert_id');
        $servieid = $request->input('service_id');
        $order = DB::table('experts')
            ->leftJoin('experts_services', 'experts_services.expert_id', '=', 'experts.id')
            ->leftJoin('services', 'services.id', '=', 'experts_services.service_id')
            ->select('experts.id as expert_id','experts.icon','experts.name','experts.job_id','experts_services.limit_free','experts_services.pay_type','experts_services.price','experts_services.pre_price','services.name as service_name')
            ->where([['experts.id',$expertid],['services.id',$servieid]])
            ->first();
        //dd($order);

        $config = require base_path('config/fieldDictionary.php');
        $order->job = $config['job'][$order->job_id];

        //unset($topic->province_id,$topic->city_id,$topic->advice);
        //dd($order);
        return api_success($order);
    }
}