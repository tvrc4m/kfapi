<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-15
 * Time: 下午3:30
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuesCollectQuesSuggest;
use App\Models\QuestionSuggest;
use Illuminate\Http\Request;

class QuestionSuggestController extends Controller
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
     * 添加建议
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
            'content' => 'required|max:255',
            'sort' => 'required|numeric',
            'type' => 'required|numeric',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
            'content.required' => '内容不能为空',
            'content.max' => '内容不能超过255个字符',
            'sort.required' => '排序不能为空',
            'sort.numeric' => '排序传入参数不合法',
            'type.required' => '类型不能为空',
            'type.numeric' => '类型传入参数不合法',
        ]);

        $result = QuestionSuggest::create($request->all());
        if ($result) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 建议列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
        ]);

        $question_collection_id = $request->input('question_collection_id');

        $where = [];
        if (!empty($question_collection_id)) {
            $where['question_collection_id'] = $question_collection_id;
        }
        $questionSuggestList = QuestionSuggest::where($where)->select(['id', 'question_collection_id', 'content', 'sort', 'type'])->paginate();

        return api_success($questionSuggestList);
    }

    /**
     * 查看某个建议
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        $data = QuestionSuggest::where('id', $id)->firstOrFail();
        return api_success($data);
    }

    /**
     * 建议编辑
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id, Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
            'content' => 'required|max:255',
            'sort' => 'required|numeric',
            'type' => 'required|numeric',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
            'content.required' => '内容不能为空',
            'content.max' => '内容不能超过255个字符',
            'sort.required' => '排序不能为空',
            'sort.numeric' => '排序传入参数不合法',
            'type.required' => '类型不能为空',
            'type.numeric' => '类型传入参数不合法',
        ]);

        $questionSuggest = QuestionSuggest::where('id', $id)->firstOrFail();
        if ($questionSuggest->update($request->all())) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 建议删除
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        if (QuestionSuggest::destroy(intval($id))) {
            return api_success();
        }
        return api_error();
    }

    /*********************************************情感建议匹配关系***************************************************/

    /**
     * 添加情感建议匹配关系
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRule(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
            'question_suggest_id' => 'required|numeric',
            'suggest_rule' => 'required|array',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
            'question_suggest_id.required' => '建议ID不能为空',
            'question_suggest_id.numeric' => '建议ID不合法',
            'suggest_rule.required' => '建议规则不能为空',
            'suggest_rule.array' => '建议规则必须是数组',
        ]);
        $result = QuesCollectQuesSuggest::create($request->all());
        if ($result) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 情感建议匹配关系列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRuleList(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
        ]);

        $question_collection_id = $request->input('question_collection_id');

        $where = [];
        if (!empty($question_collection_id)) {
            $where['question_collection_id'] = $question_collection_id;
        }
        $questionSuggest = QuesCollectQuesSuggest::where($where)->select(['id', 'question_collection_id', 'question_suggest_id', 'suggest_rule'])->paginate();

        return api_success($questionSuggest);
    }

    /**
     * 查看某个情感建议匹配关系
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRuleDetail($id)
    {
        $data = QuesCollectQuesSuggest::where('id', $id)->firstOrFail();
        return api_success($data);
    }

    /**
     * 情感建议匹配关系编辑
     * @return \Illuminate\Http\JsonResponse
     */
    public function editRule($id, Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
            'question_suggest_id' => 'required|numeric',
            'suggest_rule' => 'required|array',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
            'question_suggest_id.required' => '建议ID不能为空',
            'question_suggest_id.numeric' => '建议ID不合法',
            'suggest_rule.required' => '建议规则不能为空',
            'suggest_rule.array' => '建议规则必须是数组',
        ]);

        $questionSuggest = QuesCollectQuesSuggest::where('id', $id)->firstOrFail();
        if ($questionSuggest->update($request->all())) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 情感建议匹配关系删除
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRule($id)
    {
        if (QuesCollectQuesSuggest::destroy(intval($id))) {
            return api_success();
        }
        return api_error();
    }

}
