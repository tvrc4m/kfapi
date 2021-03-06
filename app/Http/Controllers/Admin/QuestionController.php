<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionCollection;
use App\Models\QuestionOption;
use App\Models\QuestionOptionKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 问题控制器
 * @package App\Http\Controllers\Admin
 */
class QuestionController extends Controller
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
     * 新增问题
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createQuestion(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
            'title' => 'required|max:255',
            'bgimage' => 'required',
            'type' => 'required|numeric',
            'sort' => 'numeric',
            'options' => 'array',
            'options.*.name' => 'required',
            'options.*.weight' => 'numeric',
            'options.*.keyword' => 'array',
        ],[
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'question_collection_id.required' => '问题集id不能为空',
            'question_collection_id.numeric' => '问题集id必须是数字',
            'bgimage.required' => '背景图片不能为空',
            'type.required' => '类型不能为空',
            'options.array' => '问题选项格式不对',
            'options.*.name.required' => '选项名称不能为空',
            'options.*.weight.numeric' => '选项权重必须是数字',
            'options.*.keyword.array' => '选项关键词必须是数组',
        ]);

        $question = new Question();

        try {
            $question->saveQuestion($request);
            return api_success();
        } catch (\Exception $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 修改问题
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function editQuestion($id, Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
            'title' => 'required|max:255',
            'bgimage' => 'required',
            'type' => 'required',
            'show_report' => 'required|numeric',
            'sort' => 'numeric',
            'options' => 'required|array',
            'options.*.name' => 'required',
            'options.*.weight' => 'numeric',
            'options.*.keyword' => 'array',
        ],[
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'question_collection_id.required' => '问题集id不能为空',
            'question_collection_id.numeric' => '问题集id必须是数字',
            'bgimage.required' => '背景图片不能为空',
            'type.required' => '类型不能为空',
            'show_report.required' => '类型不能为空',
            'options.required' => '问题选项不能为空',
            'options.array' => '问题选项格式不对',
            'options.*.name.required' => '选项名称不能为空',
            'options.*.weight.numeric' => '选项权重必须是数字',
            'options.*.keyword.array' => '选项关键词必须是数组',
        ]);

        $question = new Question();
        try {
            $question->saveQuestion($request, $id);
            return api_success();
        } catch (\Exception $e) {
            return api_error($e->getMessage());
        }
    }

    /**
     * 删除问题
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteQuestion($id)
    {
        $model = new Question();
        DB::beginTransaction();
        $questionInfo = $model->deleteQuestion($id);
        if ($questionInfo) {
            DB::commit();
            return api_success();
        }
        DB::rollBack();
        return api_error();
    }

    /**
     * 获得问题列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllQuestion(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
        ],[
            'question_collection_id.required' => '问题集id不能为空',
            'question_collection_id.numeric' => '问题集id必须是数字',
        ]);

        $question_collection_id = $request->input('question_collection_id');
        $list = Question::with(['questionOption'])
            ->where('question_collection_id', $question_collection_id)
            ->select(['id', 'title', 'sort', 'show_report'])
            ->orderBy('sort')
            ->orderBy('id')
            ->paginate()->toArray();


        $optionLetter = range('A', 'Z');
        if ($list){
            foreach ($list['data'] as $key=>$val){
                if ($val['question_option']){
                    foreach ($val['question_option'] as $tt_key=>$tt_val){
                        $list['data'][$key]['question_option'][$tt_key]['optionLetter'] = $optionLetter[$tt_key];
                    }
                }
            }
        }

        return api_success($list);
    }

    /**
     * 查看问题详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneQuestion($id)
    {
        $question = Question::where('id', $id)->firstOrFail();
        $options = $question->questionOption()
            ->select('id', 'options as name', 'weight')
            ->with('keyword')->get();

        $data = $question->toArray();
        $data['options'] = $options->toArray();
        foreach ($data['options'] as $k => $op) {
            $keywordArr = collect($op['keyword'])->pluck('id')->all();
            $data['options'][$k]['keyword'] = $keywordArr;
        }

        return api_success($data);
    }

    /**
     * 问题排序
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortQuestion(Request $request)
    {
        $this->validate($request, [
            'question_sort' => 'required|array',
        ],[
            'question_sort.required' => '问题不能为空',
            'question_sort.numeric' => '问题必须是数组',
        ]);

        $question_sort = $request->input('question_sort');
        if (!empty($question_sort)){
            //开启事务
            DB::beginTransaction();
            foreach ($question_sort as $key => $value) {
                $question = Question::find($value['question_id']);
                $question->sort = $value['sort'];
                $result = $question->save();
                if (!$result){
                    DB::rollBack();
                    return api_error();
                }
            }
            DB::commit();
            return api_success();
        }

        return api_success();
    }
}
