<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use App\Models\Experts;
use App\Models\ExpertsServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ExpertController extends Controller
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

    //专家列表
    public function getAllExpert(Request $request)
    {
        $type = $request->input('type');
        $experts = Experts::select(['id','icon','nickname','name','job_id','good_at'])->where('type',$type)->paginate(20)->toArray();
//dd($experts);
        $config = require base_path('config/fieldDictionary.php');

        if($experts['data']){
            //dd($goodAt);
            foreach($experts['data'] as $k=>&$v){
                $v['job'] = $config['job'][$v['job_id']];
                $goodAt = explode(',',$v['good_at']);
                unset($v['good_at'],$v['job_id']);
                //dd($goodAt);
                //dd($config['good_at']);

                foreach($goodAt as $k=>$vv){
                    //dd($vv);
                    //dd('asdsdaasdad');
                    //dd($config['good_at'][$vv]['name']);
                    if($vv){
                        $v['good_at'][] = $config['good_at'][$vv];
                    }else{
                        $v['good_at'] = [];
                    }
                }
            }
        }
        //dd($experts);
        return api_success($experts);
    }

    /**
     * 查看专家
     * @param $id
     */
    public function getOneExpert($id)
    {
        $expert = DB::table('experts')
//            ->leftJoin('experts_services', 'experts.id', '=', 'experts_services.expert_id')
//            ->leftJoin('services','services.id','=','experts_services.service_id')
            ->where('experts.id',$id)
            ->first();

        $service = DB::table('experts_services')
            ->leftJoin('services','services.id','=','experts_services.service_id')
            ->select(['experts_services.*','services.cate'])
            ->where('experts_services.expert_id',$id)
            ->get()
            ->toArray();
        //dd($service);
        $expert->service = $service;
        unset($expert->password,$expert->service_id);
        return api_success($expert);
    }

    /**
     * 新增专家
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addExpert(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'nickname' => 'required|max:255',
            'job_id' => 'required|numeric',
            'icon' => 'required',
            'certification' => 'required',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'good_at' => 'required',
            'intro' => 'required|max:255',
            'service.*.service_id' => 'required|numeric',
            'service.*.description' => 'required|max:255',
            'service.*.price' => 'required',
            'service.*.limit_free' => 'required|numeric',
            'account' => 'required|max:255',
            'password' => 'required|max:255',
            'type' => 'required|numeric'
        ],[
            'name.required' => '专家名不能为空',
            'name.max' => '专家名不能超过255个字符',
            'nickname.required' => '昵称不能为空',
            'nickname.max' => '昵称不能超过255个字符',
            'job_id.required' => '职业不能为空',
            'job_id.numeric' => '职业不合法',
            'province_id.required' => '省份不能为空',
            'province_id.numeric' => '省份不合法',
            'city_id.required' => '城市不能为空',
            'city_id.numeric' => '城市不合法',
            'icon.required' => '头像不能为空',
            'certification.required' => '认证不能为空',
            'good_at.required' => '擅长不能为空',
            'intro.required' => '介绍不能为空',
            'intro.max' => '介绍不能超过255个字符',
            'account.required' => '账号不能为空',
            'account.max' => '账号不超过255个字符',
            'password.required' => '密码不能为空',
            'password.max' => '密码不超过255个字符',
            'service.*.service_id.required' => '服务id不能为空',
            'service.*.service_id.numeric' => '服务id不合法',
            'service.*.description.required' => '服务描述不能为空',
            'service.*.description.max' => '服务描述不能超过255个字符',
            'service.*.price.required' => '服务价格不能为空',
            'service.*.limit_free.required' => '限时免费不能为空',
            'type.required' => '专家类型不能为空',
            'type.numeric' => '专家类型不合法',
        ]);
        //dd($request->only('service'));
        $data=$request->except('service');
        $data['certification'] = implode(',',$request->input('certification'));
        $data['good_at'] = implode(',',$request->input('good_at'));

        // 开启事务
        DB::beginTransaction();
        $data['password'] = Hash::make($request->input('password'));
        $expert = Experts::create($data);
        //dd($expert->id);
        $service = $request->only('service')['service'];
        foreach($service as $v){
            $v['expert_id'] = $expert->id;
            $expert_service = ExpertsServices::create($v);
            if(!$expert_service){
                DB::rollBack();
            }
        }
        DB::commit();
        return api_success();
    }

    /**
     * 删除专家
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteExpert($id)
    {
        if (Experts::destroy(intval($id))) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 修改专家
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editExpert($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'nickname' => 'required|max:255',
            'job_id' => 'required|numeric',
            'icon' => 'required',
            'certification' => 'required',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'good_at' => 'required',
            'intro' => 'required|max:255',
            'service.*.service_id' => 'required|numeric',
            'service.*.description' => 'required|max:255',
            'service.*.price' => 'required',
            'service.*.limit_free' => 'required|numeric',
            'account' => 'required|max:255',
            'type' => 'required|numeric'
        ],[
            'name.required' => '专家名不能为空',
            'name.max' => '专家名不能超过255个字符',
            'nickname.required' => '昵称不能为空',
            'nickname.max' => '昵称不能超过255个字符',
            'job_id.required' => '职业不能为空',
            'job_id.numeric' => '职业不合法',
            'province_id.required' => '省份不能为空',
            'province_id.numeric' => '省份不合法',
            'city_id.required' => '城市不能为空',
            'city_id.numeric' => '城市不合法',
            'icon.required' => '头像不能为空',
            'certification.required' => '认证不能为空',
            'good_at.required' => '擅长不能为空',
            'intro.required' => '介绍不能为空',
            'intro.max' => '介绍不能超过255个字符',
            'account.required' => '账号不能为空',
            'account.max' => '账号不超过255个字符',
            'service.*.service_id.required' => '服务id不能为空',
            'service.*.service_id.numeric' => '服务id不合法',
            'service.*.description.required' => '服务描述不能为空',
            'service.*.description.max' => '服务描述不能超过255个字符',
            'service.*.price.required' => '服务价格不能为空',
            'service.*.limit_free.required' => '限时免费不能为空',
            'type.required' => '专家类型不能为空',
            'type.numeric' => '专家类型不合法',
        ]);

        $expert = Experts::where('id',$id)->firstOrFail();
        //dd($expert);
        $service = ExpertsServices::where('expert_id',$id)->delete();
        //dd($service);
        $data=$request->except('service');
        $data['certification'] = implode(',',$request->input('certification'));
        $data['good_at'] = implode(',',$request->input('good_at'));
        $data['password'] = Hash::make($request->input('password'));
        // 开启事务
        DB::beginTransaction();

        //dd($data);
        $res = $expert->update($data);
        //dd($res);
        $newService = $request->only('service')['service'];
        //dd($newService);
        foreach($newService as $k=>$v){
            $v['expert_id'] = $id;
            $expert_service = ExpertsServices::create($v);
            //dd($expert_service);
            if(!$expert_service){
                DB::rollBack();
                return api_error();
            }
        }

        DB::commit();
        return api_success();
    }

    //职业列表
    public function getAllJob(Request $request)
    {
        $config = require base_path('config/fieldDictionary.php');
        $job = $config['job'];
        $job = array_values($job);
        return api_success($job);
    }

    //擅长列表
    public function getGoodAt(Request $request)
    {
        $config = require base_path('config/fieldDictionary.php');
        $goodAt = $config['good_at'];
        $goodAt = array_values($goodAt);
        return api_success($goodAt);
    }

    //服务列表
    public function getService(Request $request)
    {
        $cate = $request->input('cate');
        $service = DB::table('services')
            ->select('services.id','services.name')
            ->where('services.cate',$cate)
            ->get()
            ->toArray();
        //dd($service);
        return api_success($service);
    }

    //认证列表
    public function getCertification(Request $request)
    {
        $config = require base_path('config/fieldDictionary.php');
        $certification = $config['certification'];
        $certification = array_values($certification);
        return api_success($certification);
    }
}
