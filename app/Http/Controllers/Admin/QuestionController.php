<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
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
            'options' => 'required|array',
            'options.*.name' => 'required',
            'options.*.weight' => 'numeric',
            'options.*.keyword' => 'required|array',
        ],[
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'question_collection_id.required' => '问题集id不能为空',
            'question_collection_id.numeric' => '问题集id必须是数字',
            'bgimage.required' => '背景图片不能为空',
            'type.required' => '类型不能为空',
            'options.required' => '问题选项不能为空',
            'options.array' => '问题选项格式不对',
            'options.*.name.required' => '选项名称不能为空',
            'options.*.weight.numeric' => '选项权重必须是数字',
            'options.*.keyword.required' => '选项关键词必填',
            'options.*.keyword.array' => '选项关键词必须是数组',
        ]);

        // 问题基础信息
        $baseData = $request->except('options');
        // 选项信息
        $options = $request->only('options');
        // 开启事务
        DB::beginTransaction();
        // 保存基础信息
        $question = Question::create($baseData);
        if (!$question) {
            DB::rollBack();
            return api_error();
        }
        // 保存选项信息
        foreach ($options['options'] as $option) {
            $questionOption = QuestionOption::create([
                'question_id' => $question->id,
                'options' => $option['name'],
                'weight' => intval($option['weight'] ?? 0)
            ]);
            if (!$questionOption) {
                DB::rollBack();
                return api_error();
            }
            foreach ($option['keyword'] as $keyword_id) {
                // 保存关键词信息
                $keyword = QuestionOptionKeyword::create([
                    'question_option_id' => $questionOption->id,
                    'keyword_id' => $keyword_id,
                ]);
                if (!$keyword) {
                    DB::rollBack();
                    return api_error();
                }
            }
        }

        DB::commit();
        return api_success();
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
            'sort' => 'numeric',
            'options' => 'required|array',
            'options.*.name' => 'required',
            'options.*.weight' => 'numeric',
            'options.*.keyword' => 'required|array',
        ],[
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'question_collection_id.required' => '问题集id不能为空',
            'question_collection_id.numeric' => '问题集id必须是数字',
            'bgimage.required' => '背景图片不能为空',
            'type.required' => '类型不能为空',
            'options.required' => '问题选项不能为空',
            'options.array' => '问题选项格式不对',
            'options.*.name.required' => '选项名称不能为空',
            'options.*.weight.numeric' => '选项权重必须是数字',
            'options.*.keyword.required' => '选项关键词必填',
            'options.*.keyword.array' => '选项关键词必须是数组',
        ]);

        // 问题基础信息
        $baseData = $request->except('options');
        // 选项信息
        $options = $request->only('options');
        // 开启事务
        DB::beginTransaction();
        // 保存基础信息
        $question = Question::where('id', $id)->firstOrFail();
        if (!$question->update($baseData)) {
            DB::rollBack();
            return api_error();
        }
        // 删除原来的选项信息
        $ops = QuestionOption::where('question_id', $question->id)->with('keyword')->get();
        foreach ($ops as $op) {
            $del = $op->keyword()->detach();
            if (!$del) {
                DB::rollBack();
                return api_error();
            }
        }
        $del = QuestionOption::where('question_id', $question->id)->delete();
        if (!$del) {
            DB::rollBack();
            return api_error();
        }
        // 保存选项信息
        foreach ($options['options'] as $option) {
            // 保存新的选项信息
            $questionOption = QuestionOption::create([
                'question_id' => $question->id,
                'options' => $option['name'],
                'weight' => intval($option['weight'] ?? 0)
            ]);
            if (!$questionOption) {
                DB::rollBack();
                return api_error();
            }
            foreach ($option['keyword'] as $keyword_id) {
                // 保存关键词信息
                $keyword = QuestionOptionKeyword::create([
                    'question_option_id' => $questionOption->id,
                    'keyword_id' => $keyword_id,
                ]);
                if (!$keyword) {
                    DB::rollBack();
                    return api_error();
                }
            }
        }

        DB::commit();
        return api_success();
    }

    /**
     * 删除问题
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteQuestion($id)
    {
        DB::beginTransaction();
        $del = Question::destroy($id);
        if (!$del) {
            DB::rollBack();
            return api_error();
        }
        $ops = QuestionOption::where('question_id', $id)->with('keyword')->get();
        foreach ($ops as $op) {
            $del = $op->keyword()->detach();
            if (!$del) {
                DB::rollBack();
                return api_error();
            }
        }
        $del = QuestionOption::where('question_id', $id)->delete();
        if (!$del) {
            DB::rollBack();
            return api_error();
        }
        DB::commit();
        return api_success();
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
            ->select(['id', 'title', 'sort'])
            ->orderBy('sort')
            ->paginate();

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
        $options = $question->questionOption()->with('keyword')->get();

        $data = $question->toArray();
        $data['options'] = $options->toArray();

        return api_success($data);
    }
}
