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
     * 应该被转换成原生类型的属性。
     *
     * @var array
     */
    protected $casts = [
        'law_rule_ids' => 'array',
        'case_ids' => 'array',
        'suggest_ids' => 'array',
    ];

    /**
     * 生成报告书
     * @param UserAnswer $paper
     * @return bool|Model
     * @throws \Exception
     */
    public function makeReport(UserAnswer $paper)
    {
        if ($paper->type == QuestionCollection::TYPE_EMOTION) { // 情感
            return $this->makeEmotionReport($paper);
        } elseif ($paper->type == QuestionCollection::TYPE_LAW) { // 法规
            return $this->makeLawReport($paper);
        }
    }

    /**
     * 生成情感报告
     * @param UserAnswer $paper
     * @return bool
     * @throws \Exception
     */
    private function makeEmotionReport(UserAnswer $paper)
    {
        $answers = $paper->data;
        // 所有问题集ids
        $collect_ids = [];
        // 键名为问题集id 键值为答案
        $collect_id_answer = [];
        // 提取数据
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
            $v = (array)$v;
            $suggest_rule = json_decode($v['suggest_rule'], true);
            $ok = $paper->compareRule(
                $suggest_rule,
                $collect_id_answer[$v['question_collection_id']]
            );
            if ($ok) {
                $suggest_ids[] = $v['question_suggest_id'];
            }
        }
        // 保存情感建议
        DB::beginTransaction();
        $report = $this->updateOrCreate(['user_answer_id' => $paper->id], [
            'user_id' => Auth::id(),
            'suggest_ids' => $suggest_ids,
            'type' => QuestionCollection::TYPE_EMOTION,
        ]);
        if (!$report) {
            DB::rollBack();
            return false;
        }
        if (! $this->relationTopic($report->id, $paper->id)) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 关联问题
     * @param $report_id
     * @param $paper_id
     * @return bool
     */
    private function relationTopic($report_id, $paper_id)
    {
        $topic = Topics::where([
            'user_id' => Auth::id(),
            'user_answer_id' => $paper_id,
        ])->firstOrFail();

        $topic->opinion_id = $report_id;
        return $topic->save();
    }

    /**
     * 生成法规报告
     * @param UserAnswer $paper
     * @return Model
     */
    private function makeLawReport(UserAnswer $paper)
    {
        // 提取用户回答的关键词
        $answers = $paper->data;
        $option_ids = [];
        $question_ids = [];
        foreach ($answers as $collection) {
            foreach ($collection['answer'] as $v) {
                if (in_array($v['type'], [1,2,3])) { // 只有单选 多选 下拉列表 有关键词
                    $option_ids = array_merge($option_ids, $v['option_id']);
                }

                $question_ids[] = $v['question_id'];
            }
        }
        $user_keyword_ids = QuestionOptionKeyword::whereIn('question_option_id', $option_ids)
            ->get()
            ->pluck('keyword_id')->all();
        $user_keyword_ids = array_unique($user_keyword_ids);
        // 根据关键词匹配案例  获得建议
        $case_ids = $this->matchCase($user_keyword_ids);

        // 根据关键词匹配法规
        $law_rule_ids = $this->matchLaw($user_keyword_ids);

        // 组合经调查了解的内容
        // 替换模板中的内容
        $template = DB::table('report_template')->first()->content;

        $questionTitleArr = Question::whereIn('id', $question_ids)->get(['id,title'])->pluck('title', 'id')->all();
        $optionTitleArr = QuestionOption::whereIn('id', $option_ids)->get(['id', 'options'])->pluck('options', 'id')->all();
        $understand = "";
        foreach ($answers as $collection) {
            foreach ($collection['answer'] as $v) {
                $str = $questionTitleArr[$v['question_id']] . "【" . $optionTitleArr[$v['option_id']]."】";
                $understand .= $str;
            }
        }
        // 保存结果
        DB::beginTransaction();
        $report = $this->updateOrCreate(['user_answer_id' => $paper->id], [
            'user_id' => Auth::id(),
            'case_ids' => $case_ids,
            'law_rule_ids' => $law_rule_ids,
            'understand' => $understand,
            'type' => QuestionCollection::TYPE_LAW,
        ]);
        if (!$report) {
            DB::rollBack();
            return false;
        }
        if (! $this->relationTopic($report->id, $paper->id)) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 根据关键词匹配案例
     * @param $user_keyword_ids
     * @return array
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
        $case_ids = [];
        foreach ($percentArrSorted as $v) {
            if (count($case_ids) < 3) { // 只取得相似度前三
                $case_ids[] = $v['case_id'];
            }
        }

        return $case_ids;
    }

    /**
     * 根据关键词匹配法规
     * @param $user_keyword_ids
     * @return array
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
        $law_rule_ids = [];
        foreach ($percentArrSorted as $v) {
            if (count($law_rule_ids) < 3) { // 只取得相似度前三
                $law_rule_ids[] = $v['law_rule_id'];
            }
        }

        return $law_rule_ids;
    }
}