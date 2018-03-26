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
use App\Models\Question;
use App\Models\QuestionCollection;
use App\Models\QuestionOption;
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
            'title' => 'required|max:255',
            'content' => 'required',
            'sort' => 'required|numeric',
            'type' => 'required|numeric',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
            'title.required' => '建议标题不能为空',
            'title.max' => '建议标题不能超过255个字符',
            'content.required' => '内容不能为空',
            'sort.required' => '排序不能为空',
            'sort.numeric' => '排序传入参数不合法',
            'type.required' => '类型不能为空',
            'type.numeric' => '类型传入参数不合法',
        ]);
        $question_collection_id = $request->input('question_collection_id');
        $info = QuestionCollection::where(['id'=>$question_collection_id])->select(['id', 'type'])->get()->toArray();
        $count = QuestionSuggest::where(['question_collection_id'=>$question_collection_id])->count();
        if ($info && (3==intval($info[0]['type'])) && (2 == intval($count))){
            return api_error('首页问题集最多只能加两个建议!');
        }
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
        $questionSuggestList = QuestionSuggest::where($where)->select(['id', 'question_collection_id', 'title', 'content', 'sort', 'type'])->paginate();

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
            'title' => 'required|max:255',
            'content' => 'required',
            'sort' => 'required|numeric',
            'type' => 'required|numeric',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
            'title.required' => '建议标题不能为空',
            'title.max' => '建议标题不能超过255个字符',
            'content.required' => '内容不能为空',
            'sort.required' => '排序不能为空',
            'sort.numeric' => '排序传入参数不合法',
            'type.required' => '类型不能为空',
            'type.numeric' => '类型传入参数不合法',
        ]);
        $question_collection_id = $request->input('question_collection_id');
        $info = QuestionCollection::where(['id'=>$question_collection_id])->select(['id', 'type'])->get()->toArray();
        $count = QuestionSuggest::where(['question_collection_id'=>$question_collection_id])->count();
        if ($info && (3==intval($info[0]['type'])) && (2 == intval($count))){
            return api_error('首页问题集最多只能加两个建议!');
        }
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
        $question_collection_id = $request->input('question_collection_id');
        $question_suggest_id = $request->input('question_suggest_id');
        $suggest_rule = $request->input('suggest_rule');

        $result = QuesCollectQuesSuggest::updateOrCreate(['question_collection_id' => $question_collection_id, 'suggest_rule' => $suggest_rule], ['question_suggest_id' => $question_suggest_id]);
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
        $questionSuggest = QuesCollectQuesSuggest::where($where)->select(['id', 'question_collection_id', 'question_suggest_id','suggest_rule'])->paginate()->toArray();
        //$questionSuggest = QuestionSuggest::where('question_collection_id', $question_collection_id)->select(['id', 'question_collection_id', 'content', 'title', 'sort'])->paginate()->toArray();
        if ($questionSuggest['data']){
            foreach ($questionSuggest['data'] as $key=>$val){
                //$questionSuggest['data'][$key]['question'] = Question::where('question_collection_id', $val['question_collection_id'])->with('questionOption')->get()->toArray();
                $questionInfo = QuestionSuggest::where('id', $val['question_suggest_id'])->get()->toArray();
                $questionSuggest['data'][$key]['suggestion_title'] = $questionInfo[0]['title'] ?? '';
                $questionSuggest['data'][$key]['suggestion_content'] = $questionInfo[0]['content'] ?? '';
                if ($val['suggest_rule']){
                    foreach ($val['suggest_rule'] as $ru_key=>$ru_val){
                        $questionSuggest['data'][$key]['suggest_rule'][$ru_key]['question_title'] = Question::where('id', $ru_val['question_id'])->get()->toArray()[0]['title'] ?? '';
                        $questionSuggest['data'][$key]['suggest_rule'][$ru_key]['option_name'] = QuestionOption::where('id', $ru_val['option_id'])->get()->toArray()[0]['options'] ?? '';
                    }
                }
            }
        }
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
        $question_collection_id = $request->input('question_collection_id');
        $question_suggest_id = $request->input('question_suggest_id');
        $suggest_rule = $request->input('suggest_rule');

        $result = QuesCollectQuesSuggest::updateOrCreate(['question_collection_id' => $question_collection_id, 'suggest_rule' => $suggest_rule], ['question_suggest_id' => $question_suggest_id]);
        //$questionSuggest = QuesCollectQuesSuggest::where('id', $id)->firstOrFail();
        if ($result) {
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
        if (QuesCollectQuesSuggest::where('id', $id)->forceDelete()) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 情感建议匹配关系显示
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestionList(Request $request)
    {
        $this->validate($request, [
            'question_collection_id' => 'required|numeric',
        ], [
            'question_collection_id.required' => '问题集ID不能为空',
            'question_collection_id.numeric' => '问题集ID不合法',
        ]);

        $question_collection_id = $request->input('question_collection_id');

        $backData = [];
        $backData['question'] = Question::where('question_collection_id', $question_collection_id)->with('questionOption')->get()->toArray();
        $backData['suggestion'] = QuestionSuggest::where('question_collection_id', $question_collection_id)->get()->toArray();

        return api_success($backData);

    }

}
