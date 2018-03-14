<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-13
 * Time: 下午2:39
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\QuestionCollection;
use App\Models\QuesOpQuesCollect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 问题集控制器
 * @package App\Http\Controllers\Admin
 */
class QuestionCollectionController extends Controller
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
     * 新增问题集
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
            'is_trunk' => 'required',
            'title' => 'required|max:255',
            'content' => 'required|max:255',
            'is_single_page' => 'required',
            'overdue' => 'required|max:255',
            'question_option_id' => 'required|max:255',
        ],[
            'type.required' => '类型不能为空',
            'is_trunk.required' => '分支不能为空',
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'content.required' => '标题不能为空',
            'content.max' => '标题不能超过255个字符',
            'is_single_page.required' => '不能为空',
            'overdue.required' => '标题不能为空',
            'overdue.max' => '标题不能超过255个字符',
            'question_option_id.required' => '前置问题集ID不能为空',
            'question_option_id.max' => '前置问题集ID不能超过255个字符',
        ]);
        //开启事务
        $question_option_id = $request->input('question_option_id');
        $data = $request->except('question_option_id');
        $data['create_user_id'] = Auth::guard("admin")->user()->id;
        DB::beginTransaction();
        $result = QuestionCollection::create($data);
        if ($result) {
            if (!empty($question_option_id)){
                foreach ($question_option_id as $key=>$val){
                    $result2 = QuesOpQuesCollect::create(['question_collection_id' => $result->id, 'question_option_id'=> $val]);
                    if (!$result2){
                        DB::rollBack();
                        return api_error();
                    }
                }
            }
        }
        DB::commit();
        return api_success();
    }

    /**
     * 问题集列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|numeric',
        ],[
            'type.required' => '法规不能为空',
            'type.numeric' => '法规不合法',
        ]);

        $type = $request->input('type');

        $where = [];
        if (!empty($type)) {
            $where['type'] = intval($type);
        }
        $list = QuestionCollection::where($where)->select(['id','title', 'content', 'is_single_page', 'bgimage', 'is_trunk',
            'type', 'overdue'])->paginate();

        return api_success($list);
    }

    /**
     * 查看某个问题集
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        $collect = QuestionCollection::where('id', $id)->firstOrFail();
        $options = $collect->questionOption()->with('question')->get()->toArray();
        $relate_question = [];
        if ($options){
            foreach ($options as $key=>$val){
                $result2 = QuestionCollection::where('id', $val['question']['question_collection_id'])->firstOrFail()->toArray();
                if ($result2){
                    $relate_question[$key]['question_collection_id'] = $result2['id'];
                    $relate_question[$key]['question_collection_name'] = $result2['title'];
                    $relate_question[$key]['question_id'] = $val['question']['id'];
                    $relate_question[$key]['question_name'] = $val['question']['title'];
                    $relate_question[$key]['option_id'] = $val['id'];
                    $relate_question[$key]['options'] = $val['options'];
                }
            }
        }
        $backdata = $collect->toArray();
        $backdata['relate_question'] = $relate_question;
        return api_success($backdata);
    }

    /**
     * 问题集编辑
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id, Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
            'is_trunk' => 'required',
            'title' => 'required|max:255',
            'content' => 'required|max:255',
            'is_single_page' => 'required',
            'overdue' => 'required|max:255',
            'question_option_id' => 'required|max:255',
        ],[
            'type.required' => '类型不能为空',
            'is_trunk.required' => '分支不能为空',
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'content.required' => '标题不能为空',
            'content.max' => '标题不能超过255个字符',
            'is_single_page.required' => '不能为空',
            'overdue.required' => '标题不能为空',
            'overdue.max' => '标题不能超过255个字符',
            'question_option_id.required' => '前置问题集ID不能为空',
            'question_option_id.max' => '前置问题集ID不能超过255个字符',
        ]);
        //开启事务
        $question_option_id = $request->input('question_option_id');
        $data = $request->except('question_option_id');
        $data['create_user_id'] = Auth::guard("admin")->user()->id;
        DB::beginTransaction();
        $quesCollect = QuestionCollection::where('id', $id)->firstOrFail();
        $result = $quesCollect->update($data);
        if ($result) {
            if (!empty($question_option_id)){
                if (!QuesOpQuesCollect::where('question_collection_id', $id)->delete()) {
                    DB::rollBack();
                    return api_error();
                }
                foreach ($question_option_id as $key=>$val){
                    $result2 = QuesOpQuesCollect::create(['question_collection_id' => $id, 'question_option_id'=> $val]);
                    if (!$result2){
                        DB::rollBack();
                        return api_error();
                    }
                }
            }
        }
        DB::commit();
        return api_success();
    }
}
