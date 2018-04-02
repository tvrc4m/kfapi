<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Topics;
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
        //dd($request->header());
        file_put_contents('/tmp/topic.log',$request->header('device'));
        $perpage = $request->input('per_page');

        $topics = DB::table('topics')
            ->leftJoin('users', 'users.id', '=', 'topics.user_id')
            ->select('topics.id','topics.cate','topics.content','topics.comments','topics.created_at','users.user_name','users.id as user_id','users.province_id','users.city_id')
            ->where('topics.is_hide',1)
            ->orderBy('topics.created_at','desc')
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
                $v->created_at = date('m-d H:i',strtotime($v->created_at));
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
            ->leftJoin('user_question_report', 'user_question_report.id', '=', 'topics.opinion_id')
            ->select('topics.id','topics.cate','topics.content','topics.comments','topics.created_at','users.user_name','users.id','users.province_id','users.city_id','topics.opinion_id','user_question_report.suggest_ids','user_question_report.case_ids')
            ->where('topics.id',$id)
            ->first();
        //dd($topic);
        $newSuggest = '';
        if($topic->cate==1){
            //dd(json_decode($topic->case_ids));
            if(!empty($topic->case_ids)){

                $case_ids = json_decode($topic->case_ids);
                $suggest = DB::table('cases')
                    ->select('cases.suggest')
                    ->whereIn('cases.id',$case_ids)
                    ->get()
                    ->toArray();
                foreach ($suggest as $k=>$v){
                    $newSuggest .= $v->suggest.',';
                }
            }
            //dd($suggest);
        }else{
            if(!empty($topic->suggest_ids)){
                $suggest_ids = json_decode($topic->suggest_ids);
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
//dd($newSuggest);
        $topic->opinion_content = mb_substr($newSuggest,0,20);

        $city = DB::select('select p.name as provincename,c.name as cityname from bu_provinces as p left join bu_citys as c on c.provinceid= p.id where c.provinceid =? and c.cityid=?',[$topic->province_id,$topic->city_id]);
        if($city){
            $topic->area = $city[0]->provincename.$city[0]->cityname;
        }else{
            $topic->area = '';
        }

        if($topic->cate == 1){
            $topic->cate = '法律';
        }elseif($topic->cate == 2){
            $topic->cate = '情感';
        }else{
            $topic->cate = '';
        }
        $topic->created_at = date('m-d H:i',strtotime($topic->created_at));
        unset($topic->province_id,$topic->city_id,$topic->suggest_ids,$topic->case_ids);
        //dd($topic);
        return api_success($topic);
    }

    //用户提交问题
    public function addTopic(Request $request)
    {
        $this->validate($request, [
            'paper_id' => 'required|numeric',
            'cate' => 'required|numeric',
            'content' => 'required',
            'description' => 'required',
        ],[
            'paper_id.required' => '答卷ID不能为空',
            'paper_id.numeric' => '答卷ID不合法',
            'cate.required' => '问题分类不能为空',
            'cate.numeric' => '问题分类不合法',
            'content.required' => '问题不能为空',
//            'content.max' => '问题不超过500个字符',
            'description.required' => '情感描述不能为空',
//            'description.max' => '情感描述不超过500个字符',
        ]);

        $userid = \Auth::user()['id'];
        $data = array(
            'user_id'=>$userid,
            'cate'=>$request->input('cate'),
            'user_answer_id'=>$request->input('paper_id'),
            'content'=>$request->input('content'),
            'description'=>$request->input('description'),
        );
        $result = Topics::create($data);
        if ($result) {
            return api_success($result);
        }
        return api_error();
    }

    //修改问题状态
    public function editStatus(Request $request)
    {
        $status = $request->input('is_hide');
        $topic_id = $request->input('topic_id');
        $userId = \Auth::user()['id'];
        //dd($userId);
        $topic = Topics::where('id',$topic_id)->first();
        //dd($topic);

        if(!$topic){
            return api_error('没有此问题');
        }
        $data = array(
            'is_hide'=>$status,
        );
        // 开启事务
        DB::beginTransaction();
        $result1 = $topic->update($data);
        $opinionId = $topic->opinion_id??0;
        //dd($opinionId);
        if($opinionId){
            $result2 = DB::table('user_question_report')->where(['user_id'=>$userId,'id'=>$opinionId])->update($data);
        }
        if (!$result1) {
            DB::rollBack();
            return api_error('修改问题状态失败');
        }
        DB::commit();


        return api_success();
    }
}