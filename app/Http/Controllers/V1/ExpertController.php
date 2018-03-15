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

        //配置文件获取专家职业
        $config = require APP_PATH . 'config/fieldDictionary.php';
        //dd($config);
        $jobs = $config['job'];

        $list = Experts::select(['id','name','icon', 'job_id', 'intro','type'])->paginate($per_page)->toArray();
        $data['law_list'] = [];
        $data['feel_list'] = [];
        if($list){
            foreach ($list['data'] as $k=>$v){
                $job = $jobs[$v['job_id']];

                if($v['type']==1){
                    $data['law_list'][$k]['id'] = $v['id'];
                    $data['law_list'][$k]['name'] = $v['name'];
                    $data['law_list'][$k]['icon'] = $v['icon'];
                    $data['law_list'][$k]['job'] = $job;
                    $data['law_list'][$k]['intro'] = $v['intro'];
                }
                if($v['type']==2){
                    $data['feel_list'][$k]['id'] = $v['id'];
                    $data['feel_list'][$k]['name'] = $v['name'];
                    $data['feel_list'][$k]['icon'] = $v['icon'];
                    $data['feel_list'][$k]['job'] = $job;
                    $data['feel_list'][$k]['intro'] = $v['intro'];
                }
            }
        }
        //dd($data);
        return api_success($data);
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
        //组装城市
        $city = DB::select('select p.name as provincename,c.name as cityname from bu_provinces as p left join bu_citys as c on c.provinceid= p.id where c.provinceid =? and c.cityid=?',[$data->province_id,$data->city_id]);
        //dd($city);
        $data->area = $city[0]->provincename.$city[0]->cityname;

        //$data->service['']

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

        //dd($certification);
        //dd($data);
        return api_success($data);
    }
}