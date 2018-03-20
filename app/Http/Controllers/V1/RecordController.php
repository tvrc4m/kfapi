<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Topics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RecordController extends Controller
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
     * 用户评测记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRecord(Request $request)
    {
        $userid = Auth::user()->toArray()['id'];
        //dd($userid);
        $perpage = $request->input('per_page');
        $record = DB::table('topics')
            ->leftJoin('users', 'users.id', '=', 'topics.user_id')
            ->select('topics.cate','topics.content','topics.created_at','users.user_name','users.province_id','users.city_id')
            ->where('topics.user_id',$userid)
            ->paginate($perpage)
            ->toArray();

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
        //dd($cArr);
        if($record['data']){
            foreach ($record['data'] as $k=>&$v){
                //dd($city);
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

        //dd($data);
        return api_success($record);
    }

    /**
     * 删除用户评测记录
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRecord(Request $request)
    {
        //dd($request->all());
        $topicIds = $request->input('topic_id');
        //dd($topicIds);
        $ids = explode(',',$topicIds);
        $allIds = Topics::select('id')->get()->toArray();
        //dd($allIds);
        foreach($allIds as $k=>$v){
            $newIds[] = $v['id'];
        }
        //dd($newIds);
        //dd($ids);
        foreach($ids as $k=>$v){
            //dd($v);
            if(in_array(intval($v),$newIds)){
                $result = Topics::destroy(intval($v));
                if(!$result){
                    return api_error();
                }
            }
        }
        return api_success();
    }

    /**
     * 用户评测记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllOpinion(Request $request)
    {
        $userid = Auth::user()->toArray()['id'];
        //dd($userid);
        $perpage = $request->input('per_page');
        $record = DB::table('user_question_report')
            ->select('id','user_question_report.case_ids','user_question_report.suggest_ids','user_question_report.type','created_at','updated_at')
            ->where('user_id',$userid)
            ->paginate($perpage)
            ->toArray();
//dd($record);
        //dd($cArr);
        $newSuggest = '';
        if($record['data']){
            foreach ($record['data'] as $k=>&$opinion){
                if($opinion->type==1){
                    //dd(json_decode($topic->case_ids));
                    if(!empty($opinion->case_ids)){
                        $case_ids = json_decode($opinion->case_ids);
                        $suggest = DB::table('cases')
                            ->select('cases.suggest')
                            ->whereIn('cases.id',$case_ids)
                            ->get()
                            ->toArray();
                        //dd($suggest);
                        foreach ($suggest as $k=>$v){
                            $newSuggest .= $v->suggest.'。';
                        }
                    }
                    //dd($suggest);
                }else{
                    if(!empty($opinion->suggest_ids)){
                        $suggest_ids = json_decode($opinion->suggest_ids);
                        $suggest = DB::table('question_suggests')
                            ->select('question_suggests.content')
                            ->whereIn('question_suggests.id',$suggest_ids)
                            ->get()
                            ->toArray();
                        foreach ($suggest as $k=>$v){
                            $newSuggest .= $v->content.'。';
                        }
                    }
                }
                $opinion->opinion_content = mb_substr($newSuggest,0,60);
                //dd($city);
                if($opinion->type == 1){
                    $opinion->type = '法律';
                }elseif($opinion->type == 2){
                    $opinion->type = '情感';
                }else{
                    $opinion->type = '';
                }
                unset($opinion->case_ids,$opinion->suggest_ids);
            }
        }

        //dd($data);
        return api_success($record);
    }

    /**
     * 删除用户评测记录
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOpinion(Request $request)
    {
        //dd($request->all());
        $opinionIds = $request->input('opinion_id');
        //dd($topicIds);
        $ids = explode(',',$opinionIds);
        $allIds = DB::table('user_question_report')
                ->select('id')->get()->toArray();
        //dd($allIds);
        foreach($allIds as $k=>$v){
            $newIds[] = $v['id'];
        }
        //dd($newIds);
        //dd($ids);
        foreach($ids as $k=>$v){
            //dd($v);
            if(in_array(intval($v),$newIds)){
                $result = Topics::destroy(intval($v));
                if(!$result){
                    return api_error();
                }
            }
        }
        return api_success();
    }

}
