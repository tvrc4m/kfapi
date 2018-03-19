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
        // 提取关键词
        // 匹配法规
        // 匹配案例
        // 匹配建议
        // 组合经调查了解
    }
}