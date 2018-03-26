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
            ->select('topics.id','topics.cate','topics.content','topics.created_at','users.user_name','users.province_id','users.city_id')
            ->where('topics.user_id',$userid)
            ->paginate($perpage)
            ->toArray();
//dd($record);
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
                if($v->province_id && $v->city_id){
                    $v->area = $pArr[$v->province_id].$cArr[$v->city_id];
                }else{
                    $v->area = '';
                }
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

        $record['user_id'] = $userid;
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
            }else{
                return api_error('没有此ID');
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
                            $newSuggest .= $v->suggest.',';
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
                            $newSuggest .= $v->content.',';
                        }
                    }
                }
                $opinion->opinion_content = mb_substr($newSuggest,0,100);
                //dd($city);
                unset($opinion->case_ids,$opinion->suggest_ids);
            }
        }
        $record['user_id'] = $userid;
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
        $user_id = Auth::user()['id'];
        //dd($user_id);
        $opinionIds = $request->input('opinion_id');
        //dd($opinionIds);
        $ids = explode(',',$opinionIds);
        //dd($ids);
        $allIds = DB::table('user_question_report')
                ->select('id')
                ->where('user_id',$user_id)
                ->get()->toArray();
        //dd($allIds);
        if($allIds){
            foreach($allIds as $k=>$v){
                $newIds[] = $v->id;
            }
            foreach($ids as $k=>$v){
                //dd($v);
                if(in_array(intval($v),$newIds)){
                    //dd($v);
                    $result = DB::table('user_question_report')->where('id',intval($v))->delete();
                    //dd(2123);
                    if(!$result){
                        return api_error();
                    }
                }else{
                    return api_error('没有此ID');
                }
            }
        }
        //dd($newIds);
        //dd($ids);

        return api_success();
    }

    /**
     * 查看意见书详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneOpinion($opinionId)
    {
        //dd($user_id);
        //dd($topicIds);
        $opinion = DB::table('user_question_report')
            ->select('id','law_rule_ids','user_question_report.case_ids','user_question_report.suggest_ids','user_question_report.type','understand','remark')
            ->where('id',$opinionId)
            ->first();
        if($opinion->type==1){
            $lawRuleIds = json_decode($opinion->law_rule_ids);
            //dd($lawRuleIds);
            $rule = DB::table('law_rules')
                ->Join('laws','laws.id','=','law_rules.law_id')
                ->select('law_rules.title','law_rules.content','laws.fullname')
                ->whereIn('law_rules.id',$lawRuleIds)
                ->get()
                ->toArray();
            //dd($rule);
            $caseIds = json_decode($opinion->case_ids);
            $advice = DB::table('cases')
                ->select(['suggest','judgment'])
                ->whereIn('id',$caseIds)
                ->get()
                ->toArray();
            if($advice){
                foreach($advice as $k=>$v){
                    $suggest[] = $v->suggest;
                    $judgment[]= $v->judgment;
                }
            }
            //dd($suggest);
            $data = array(
                'id'=>$opinion->id,
                'type'=>$opinion->type,
                'remark'=>$opinion->remark,
                'law_rule'=>['name'=>'一、可参考法规','content'=>$rule],
                'understand'=>['name'=>'二、经调查了解','content'=>$opinion->understand],
                'suggest'=>['name'=>'三、本地建议如下','content'=>$suggest],
                'judgment'=>['name'=>'四、综上所述','content'=>$judgment]
            );
        }else{
            $suggestIds = json_decode($opinion->suggest_ids);
            $suggest = DB::table('question_suggests')
                ->select('content')
                ->whereIn('id',$suggestIds)
                ->get()
                ->toArray();
            //dd($suggest);
            $data = array(
                'id'=>$opinion->id,
                'type'=>$opinion->type,
                'remark'=>$opinion->remark,
                'suggest'=>['name'=>'经调查了解','content'=>$suggest],
            );
           // dd($data);
        }
        //dd($data);
        //dd($opinion);
        return api_success($data);
    }
}
