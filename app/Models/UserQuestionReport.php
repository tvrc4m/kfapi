<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserQuestionReport extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "user_question_report";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 生成报告书
     * @param UserAnswer $paper
     * @return string|void
     */
    public function makeReport(UserAnswer $paper)
    {
        if ($paper->type == QuestionCollection::TYPE_EMOTION) { // 情感
            $this->makeEmotionReport($paper);
        } elseif ($paper->type == QuestionCollection::TYPE_LAW) { // 法规
            $this->makeLawReport($paper);
        }
    }

    /**
     * 生成情感报告
     * @param UserAnswer $paper
     * @return string
     */
    private function makeEmotionReport(UserAnswer $paper)
    {
        $answers = json_decode($paper->data, true);
        // 所有问题集ids
        $collect_ids = [];
        // 键名为问题集id 键值为答案
        $collect_id_answer = [];
        // 提起数据
        foreach ($answers as $v) {
            $collect_ids[] = $v['question_collection_id'];
            $collect_id_answer[$v['question_collection_id']] = $v['answer'];
        }
        // 获得所有建议
        $relations = DB::table('question_collection_question_suggests')
            ->whereIn('question_collection_id', $collect_ids)
            ->get()->toArray();
        // 匹配建议
        $suggest_ids = [];
        foreach ($relations as $v) {
            $ok = $paper->compareRule(
                $v['suggest_rule'],
                $collect_id_answer[$v['question_collection_id']]
            );
            if ($ok) {
                $suggest_ids[] = $v['question_suggest_id'];
            }
        }
        // 组合所有建议
        $suggests = QuestionSuggest::whereIn('id', $suggest_ids)->orderBy('sort')->get();
        $suggestStr = '';
        foreach ($suggests as $v) {
            $suggestStr .= $v['content'];
        }

        $this->updateOrCreate(['user_answer_id' => $paper->id], [
            'user_id' => Auth::id(),
            'suggest' => $suggestStr,
            'type' => QuestionCollection::TYPE_EMOTION,
        ]);
    }

    /**
     * 生成法规报告
     * @param UserAnswer $paper
     */
    private function makeLawReport(UserAnswer $paper)
    {
        // 提取用户回答的关键词
        $answers = json_decode($paper->data, true);
        $option_ids = [];
        $question_ids = [];
        foreach ($answers as $collection) {
            foreach ($collection['answer'] as $v) {
                $option_ids[] = $v['option_id'];
                $question_ids[] = $v['question_id'];
            }
        }
        $user_keyword_ids = QuestionOptionKeyword::whereIn('question_option_id', $option_ids)
            ->get()
            ->pluck('keyword_id');
        $keyword_ids = array_unique($user_keyword_ids);
        sort($keyword_ids, SORT_NUMERIC);
        // 根据关键词匹配案例  获得建议
        $this->matchCase($user_keyword_ids);

        // 根据关键词匹配法规
        $this->matchLaw($user_keyword_ids);

        // 组合经调查了解的内容

    }

    /**
     * 根据关键词匹配案例
     * @param $user_keyword_ids
     */
    private function matchCase($user_keyword_ids)
    {
        $case_keywords = CaseKeyword::get(['case_id', 'keyword_id']);
        $caseKeywordArr = [];
        foreach ($case_keywords as $v) {
            if (!isset($caseKeywordArr[$v['case_id']])) {
                $caseKeywordArr[$v['case_id']] = [];
            }
            if (!in_array($v['keyword_id'], $caseKeywordArr[$v['case_id']])) {
                $caseKeywordArr[$v['case_id']][] = $v['keyword_id'];
                sort($caseKeywordArr[$v['case_id']], SORT_NUMERIC);
            }
        }
        // 最大相似度
        $percentArr = [];
        foreach ($caseKeywordArr as $case_id => $v) {
            $percent = similar_array($user_keyword_ids, $v);
            $percentArr[] = [
                'percent' => $percent,
                'case_id' => $case_id,
            ];
        }
        // 相似度倒序
        $percentArrSorted = arraySort($percentArr, 'percent');
    }

    /**
     * 根据关键词匹配法规
     * @param $user_keyword_ids
     */
    private function matchLaw($user_keyword_ids)
    {
        $lawKeywords = LawRuleKeyword::get(['law_rule_id', 'keyword_id']);
        $lawKeywordArr = [];
        foreach ($lawKeywords as $v) {
            if (!isset($lawKeywordArr[$v['law_rule_id']])) {
                $lawKeywordArr[$v['law_rule_id']] = [];
            }
            if (!in_array($v['keyword_id'], $lawKeywordArr[$v['law_rule_id']])) {
                $lawKeywordArr[$v['law_rule_id']][] = $v['keyword_id'];
                sort($lawKeywordArr[$v['law_rule_id']], SORT_NUMERIC);
            }
        }
        // 最大相似度
        $percentArr = [];
        foreach ($lawKeywordArr as $law_rule_id => $v) {
            $percent = similar_array($user_keyword_ids, $v);
            $percentArr[] = [
                'percent' => $percent,
                'law_rule_id' => $law_rule_id,
            ];
        }
        // 相似度倒序
        $percentArrSorted = arraySort($percentArr, 'percent');
    }
}