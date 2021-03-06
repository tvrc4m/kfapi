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
            ->orderBy('topics.created_at','desc')
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
                //dd($v->created_at);
                $v->topic_time = $this->getReadable(strtotime($v->created_at),1);
                $v->created_at = date('m-d H:i:s',strtotime($v->created_at));
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
            ->leftJoin('topics','topics.opinion_id','=','user_question_report.id')
            ->select('topics.id as topic_id','user_question_report.id','user_question_report.case_ids','user_question_report.suggest_ids','user_question_report.type','user_question_report.created_at','user_question_report.updated_at')
            ->where('user_question_report.user_id',$userid)
            ->orderBy('topics.created_at','desc')
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
                $opinion->opinion_time = $this->getReadable(strtotime($opinion->created_at),2);
                $opinion->created_at = date('m-d H:i:s',strtotime($opinion->created_at));
                $opinion->updated_at = date('m-d H:i:s',strtotime($opinion->created_at));
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
    public function getOneOpinion(Request $request,$opinionId)
    {
        $opinion = DB::table('user_question_report')
            ->select('id','law_rule_ids','user_question_report.case_ids','user_question_report.suggest_ids','user_question_report.type','understand','remark')
            ->where('id',$opinionId)
            ->first();
        //dd($opinion);
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
            $suggest = array();
            $judgment = array();
            foreach($advice as $k=>$v){
                $suggest[] = $v->suggest;
                $judgment[]= $v->judgment;
            }
            //dd($suggest);
            $data = array(
                'id'=>$opinion->id,
                'type'=>$opinion->type,
                'remark'=>$opinion->remark,
                'law_rule'=>['name'=>'可参考法规','content'=>$rule],
                'understand'=>['name'=>'经调查了解','content'=>$opinion->understand],
                'suggest'=>['name'=>'本次建议如下','content'=>$suggest],
                'judgment'=>['name'=>'综上所述','content'=>$judgment]
            );
        }else{
            $suggestIds = json_decode($opinion->suggest_ids);
            $suggest = DB::table('question_suggests')
                ->select('content')
                ->whereIn('id',$suggestIds)
                ->get()
                ->toArray();
            $newSuggest = array();
            foreach($suggest as $k=>$v){
                $newSuggest[]= $v->content;
            }
            //dd($suggest);
            $data = array(
                'id'=>$opinion->id,
                'type'=>$opinion->type,
                'remark'=>$opinion->remark,
                'suggest'=>['name'=>'本次建议如下','content'=>$newSuggest],
            );
           // dd($data);
        }
        //dd($data);
        //dd($opinion);
        return api_success($data);
    }

    /**
     ** 获取 N年，N月，N天，N小时，N分钟前 这种字符串
     * @param  [type] $time 时间戳
     * @return [type]       [description]
     */
    function getReadable($time,$type)
    {
        $j = time() - $time;
        if ($j < 0) {
            return false;
        }elseif ($j <= 1800) {
            return '您在刚刚提问';
        }else {
            $hourTime = 3600;
            $dayTime = $hourTime * 24;
            $weekTime = $dayTime * 7;
            $mouthTime = 30 * $dayTime;
            $yearTime = 365 * $dayTime;
            $it = array(
                $yearTime => '年',
                $mouthTime => '月',
                $weekTime => '周',
                $dayTime => '天',
                $hourTime => '小时',
                60 => '分钟',
                1 => '秒'
            );
            foreach ($it as $item => $value) {
                if (0 !=$c=floor($j/(int)$item)) {
                    if($type==1){
                        return '您在'.$c.$value.'前提问';
                    }else{
                        return '您在'.$c.$value.'前完成测试';
                    }
                }

            }
        }
    }
}
