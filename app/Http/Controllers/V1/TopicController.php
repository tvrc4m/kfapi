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
            ->select('topics.id','topics.cate','topics.content','topics.comments','topics.created_at','users.user_name','users.id','users.province_id','users.city_id')
            ->where('topics.is_hide',1)
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
        if($topic) {
            if ($topic->cate == 1) {
                //dd(json_decode($topic->case_ids));
                if (!empty($topic->case_ids)) {
                    $case_ids = json_decode($topic->case_ids);
                    $suggest = DB::table('cases')
                        ->select('cases.suggest')
                        ->whereIn('cases.id', $case_ids)
                        ->get()
                        ->toArray();
                    foreach ($suggest as $k => $v) {
                        $newSuggest .= $v->suggest . ',';
                    }
                }
                //dd($suggest);
            } else {
                if (!empty($topic->suggest_ids)) {
                    $suggest_ids = json_decode($topic->suggest_ids);
                    $suggest = DB::table('question_suggests')
                        ->select('question_suggests.content')
                        ->whereIn('question_suggests.id', $suggest_ids)
                        ->get()
                        ->toArray();
                    foreach ($suggest as $k => $v) {
                        $newSuggest .= $v->content . ',';
                    }
                }
            }

            $topic->opinion_content = substr($newSuggest,0,20);

            $city = DB::select('select p.name as provincename,c.name as cityname from bu_provinces as p left join bu_citys as c on c.provinceid= p.id where c.provinceid =? and c.cityid=?',[$topic->province_id,$topic->city_id]);
            if($city){
                $topic->area = $city[0]->provincename.$city[0]->cityname;
            }

            if($topic->cate == 1){
                $topic->cate = '法律';
            }elseif($topic->cate == 2){
                $topic->cate = '情感';
            }else{
                $topic->cate = '';
            }

            unset($topic->province_id,$topic->city_id,$topic->suggest_ids,$topic->case_ids);
        }else{
            return api_error('数据为空');
        }

//dd($newSuggest);
        //dd($topic);
        return api_success($topic);
    }

    //用户提交问题
    public function addTopic(Request $request)
    {
        $this->validate($request, [
            'paper_id' => 'required|numeric',
            'content' => 'required|max:255',
            'description' => 'required|max:500',
        ],[
            'paper_id.required' => '用户ID不能为空',
            'paper_id.numeric' => '用户ID不合法',
            'content.required' => '问题id不能为空',
            'content.max' => '问题不超过255个字符',
            'description.required' => '情感描述不能为空',
            'description.max' => '情感描述不超过500个字符',
        ]);

        $userid = \Auth::user()['id'];
        $data = array(
            'user_id'=>$userid,
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

        $topic = Topics::where('id',$topic_id)->first();

        $data = array(
            'is_hide'=>$status,
        );
        if($topic){
            $result = $topic->update($data);
            if(!$result){
                return api_error('修改状态失败');
            }
        }else{
            return api_error('没有此ID');
        }
        return api_success();
    }
}