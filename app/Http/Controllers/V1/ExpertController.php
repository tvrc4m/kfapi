<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Experts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * 专家列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllExpert(Request $request)
    {
        $per_page = $request->input('per_page');
        $type = $request->input('type');
        $topic_id = $request->input('topic_id');

        $user_id = \Auth::user()['id'];
//dd($user_id);
        //配置文件获取专家职业
        $config = require base_path('config/fieldDictionary.php');
        //dd($config);
        $jobs = $config['job'];
        $jobs = array_values($jobs);
        $list = Experts::select(['id','name','icon', 'job_id', 'intro','type'])->where('type',$type)->paginate($per_page)->toArray();
//dd($list);
        //dd($jobs);
        if($list['data']){
            foreach ($list['data'] as $k=>$v){
                $expert_id[] = $v['id'];
            }
        }
        //dd($expert_id);
        $invitation = DB::table('invitations')->where(['user_id'=>$user_id,'topic_id'=>$topic_id])->whereIn('expert_id',$expert_id)->get()->toArray();

        if($invitation){
            foreach($invitation as $k=>$v){
                $expertId[] = $v->expert_id;
            }
        }
        //dd($expertId);
        $newJobs = [];
        foreach($jobs as $k=>$v){
            $newJobs[$v['job_id']] = $v['name'];
        }

        if($list['data']){
            foreach ($list['data'] as $k=>$v){
                if(in_array($v['id'],$expertId)){
                    $list['data'][$k]['invi_stat'] = 1;
                }else{
                    $list['data'][$k]['invi_stat'] = 0;
                }
                if($v['job_id']){
                    //var_dump($v['job_id']);
                    //var_dump($newJobs[$v['job_id']]);
                    $job = $newJobs[$v['job_id']];
                }else{
                    $job = '';
                }
                $list['data'][$k]['id'] = $v['id'];
                $list['data'][$k]['name'] = $v['name'];
                $list['data'][$k]['icon'] = $v['icon'];
                $list['data'][$k]['job'] = $job;
                $list['data'][$k]['intro'] = $v['intro'];
            }
        }
        //dd($data);
        return api_success($list);
    }

    /**
     * 查看专家
     * @param $id
     */
    public function getOneExpert($id)
    {
        //$data = Experts::where('id', $id)->select(['name','icon','certification','good_at','intro','province_id','city_id'])->firstOrFail()->toArray();

        $data = DB::table('experts')
            ->leftJoin('experts_services', 'experts.id', '=', 'experts_services.expert_id')
            ->leftJoin('services','services.id','=','experts_services.service_id')
            ->select('experts.name as expertname','experts.icon','experts.certification','experts.good_at','experts.intro','experts.province_id','experts.city_id',
                'experts_services.expert_id','experts_services.price','experts_services.description','experts_services.limit_free','services.name','services.stat')
            ->where('experts.id',$id)
            ->first();
        //dd($data);
        if($data){
            //组装城市
            $city = DB::select('select p.name as provincename,c.name as cityname from bu_provinces as p left join bu_citys as c on c.provinceid= p.id where c.provinceid =? and c.cityid=?',[$data->province_id,$data->city_id]);
            //dd($city);
            $data->area = $city[0]->provincename.$city[0]->cityname;
            $config = require APP_PATH . 'config/fieldDictionary.php';
            //组装服务信息
            $serviceName = $config['service'][$data->name];
            $data->service = array(
                'name'=>$serviceName,
                'price'=>$data->price,
                'description'=>$data->description,
                'stat'=>$data->stat,
                'limit_free'=>$data->limit_free,
            );

            //组装擅长、认证
            $goodAt = explode(',',$data->good_at);
            $certification = explode(',',$data->certification);

            unset($data->good_at,$data->certification,$data->province_id,$data->city_id,$data->name,$data->price,$data->description,$data->stat,$data->limit_free);
            foreach ($goodAt as $k=>$v){
                $data->good_at[$k] = $config['good_at'][$v];
            }
            //dd($data['good_at']);

            foreach ($certification as $k=>$v){
                $data->certification[$k] = $config['certification'][$v];
            }
        }
        //dd($data);
        return api_success([$data]);
    }
}
