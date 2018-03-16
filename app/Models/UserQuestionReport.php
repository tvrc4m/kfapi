<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
     */
    public function makeReport(UserAnswer $paper)
    {
        if ($paper->type == 2) { // 情感
            $this->makeEmotionReport($paper);
        } elseif ($paper->type == 1) { // 法规
            $this->makeLawReport($paper);
        }
    }

    /**
     * 生成情感报告
     * @param UserAnswer $paper
     */
    private function makeEmotionReport(UserAnswer $paper)
    {
        $suggestStr = '';
        $answers = json_decode($paper->data, true);
        foreach ($answers as $v) {
            // 获得问题集的所有建议
            // QuestionCollection::where('id', $v['question_collection_id'])
            // $paper->compareRule();
        }
    }

    /**
     * 生成法规报告
     * @param UserAnswer $paper
     */
    private function makeLawReport(UserAnswer $paper)
    {

    }
}