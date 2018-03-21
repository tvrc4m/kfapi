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
use App\Models\QuestionOption;
use App\Models\Question;
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
            'bgimage' => 'required',
            'is_single_page' => 'required',
            'overdue' => 'required|max:255',
            'question_option_id' => 'required|max:255',
        ],[
            'type.required' => '类型不能为空',
            'is_trunk.required' => '分支不能为空',
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'content.required' => '描述内容不能为空',
            'content.max' => '描述内容不能超过255个字符',
            'bgimage.required' => '背景图片不能为空',
            'is_single_page.required' => '是否单页不能为空',
            'overdue.required' => '过渡页不能为空',
            'overdue.max' => '过渡页不能超过255个字符',
            'question_option_id.required' => '前置问题集ID不能为空',
            'question_option_id.array' => '前置问题集ID是数组',
        ]);
        if (!Auth::guard("admin")->user()){
            return api_error('未登录');
        }
        $questionCollection = new QuestionCollection();
        if ($questionCollection->saveQuestionCollection($request,0)) {
            return api_success();
        }

        return api_error();
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
        $list = QuestionCollection::with('adminUser')->with('questionOption')->where($where)->select(['id','title', 'content', 'is_single_page', 'bgimage', 'is_trunk',
            'type', 'overdue', 'created_at', 'sort', 'create_user_id', 'num'])->paginate()->toArray();
        $questionListData = Question::select()->get()->toArray();
        $questionList = [];
        if ($questionListData){
            foreach ($questionListData as $qu_key=>$qu_val) {
                $questionList[$qu_val['id']] = $qu_val;
            }
        }
        if ($list){
            foreach ($list['data'] as $key=>$val) {
                $list['data'][$key]['username'] = $val['admin_user']['username'];
                unset($list['data'][$key]['admin_user']);
                if ($val['question_option']){
                    foreach ($val['question_option'] as $tt_key=>$tt_val){
                        $list['data'][$key]['question_name'][$tt_key] = $questionList[$tt_val['question_id']]['title'];
                    }
                    unset($list['data'][$key]['question_option']);
                }else{
                    $list['data'][$key]['question_name'] = '';
                }
            }
        }
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
            'bgimage' => 'required',
            'is_single_page' => 'required',
            'overdue' => 'required|max:255',
            'question_option_id' => 'required|array',
        ],[
            'type.required' => '类型不能为空',
            'is_trunk.required' => '分支不能为空',
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'content.required' => '描述内容不能为空',
            'content.max' => '描述内容不能超过255个字符',
            'bgimage.required' => '背景图片不能为空',
            'is_single_page.required' => '是否单页不能为空',
            'overdue.required' => '过渡页不能为空',
            'overdue.max' => '过渡页不能超过255个字符',
            'question_option_id.required' => '前置问题集ID不能为空',
            'question_option_id.array' => '前置问题集ID是数组',
        ]);
        if (!Auth::guard("admin")->user()){
            return api_error('未登录');
        }
        $questionCollection = new QuestionCollection();
        if ($questionCollection->saveQuestionCollection($request, $id)) {
            return api_success();
        }

        return api_error();
    }

    /**
     * 所有问题集列表没有分页
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllList(Request $request)
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
            'type', 'overdue'])->get();

        return api_success($list);
    }

    /**
     * 某个问题集下面的所有问题以及对应的选项
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllQuestionList(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
        ]);

        $question_collection_id = $request->input('question_collection_id');
        $backData = Question::where('question_collection_id', $question_collection_id)->with('questionOption')->get()->toArray();

        return api_success($backData);
    }
}
