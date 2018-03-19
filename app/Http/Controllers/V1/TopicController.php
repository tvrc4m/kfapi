<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    //大家在测列表
    public function getAllTopic(Request $request)
    {
        dd($request->header());
        $perpage = $request->input('per_page');

        $topics = DB::table('topics')
            ->leftJoin('users', 'users.id', '=', 'topics.user_id')
            ->select('topics.cate','topics.content','topics.comments','topics.created_at','users.user_name','users.province_id','users.city_id')
            ->paginate($perpage)
            ->toArray();
        //dd($topics);

        //取出所有省、城市
        $provinces = DB::table('provinces')
            ->select('provinces.id','provinces.name')
            ->get()
            ->toArray();
        //dd($provinces);
        foreach($provinces as $k=>$v){
            $pArr[$v->id] = $v->name;
        }
        $city = DB::table('citys')
            ->select('citys.cityid','citys.name')
            ->get()
            ->toArray();
        foreach($city as $k=>$v){
            $cArr[$v->cityid] = $v->name;
        }

        if($topics['data']){
            foreach ($topics['data'] as $k=>&$v){
                //$v->area = $config['job'][$v->job_id];
                $v->area = $pArr[$v->province_id].$cArr[$v->city_id];
                if($v->cate == 1){
                    $v->cate = '法律';
                }elseif($v->cate == 2){
                    $v->cate = '情感';
                }else{
                    $v->cate = '';
                }
                unset($v->province_id,$v->city_id);
            }
        }

        //dd($topics);
        return api_success($topics);
    }

    /**
     * 问答详情
     * @param $id
     */
    public function getOneTopic($id)
    {
        //print_sql();
        $topic = DB::table('topics')
            ->leftJoin('users', 'users.id', '=', 'topics.user_id')
            ->leftJoin('opinions', 'opinions.id', '=', 'topics.opinion_id')
            ->select('topics.cate','topics.content','topics.comments','topics.created_at','users.user_name','users.province_id','users.city_id','topics.opinion_id','opinions.advice')
            ->where('topics.id',$id)
            ->first();

        $topic->opinion_content = substr($topic->advice,0,30);

        $city = DB::select('select p.name as provincename,c.name as cityname from bu_provinces as p left join bu_citys as c on c.provinceid= p.id where c.provinceid =? and c.cityid=?',[$topic->province_id,$topic->city_id]);
        $topic->area = $city[0]->provincename.$city[0]->cityname;

        if($topic->cate == 1){
            $topic->cate = '法律';
        }elseif($topic->cate == 2){
            $topic->cate = '情感';
        }else{
            $topic->cate = '';
        }

        unset($topic->province_id,$topic->city_id,$topic->advice);
        //dd($topic);
        return api_success($topic);
    }

}