<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAnswer extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "user_answers";

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
     * 初始化试卷
     * @param $user_id
     * @return $this|Model
     */
    private function initPaper($user_id)
    {
        // 初始化问题
        $collec = QuestionCollection::where('type', QuestionCollection::TYPE_INIT)->firstOrFail();

        return $this->create([
            'user_id' => $user_id,
            'wait_question_collection_ids' => json_encode([$collec->id]),
        ]);
    }

    /**
     * 获得试卷
     * @param $user_id
     * @return UserAnswer|Model|null|object|static
     */
    public function getPaper($user_id)
    {
        $paper = $this->where('user_id', $user_id)->orderByDesc('created_at')->first();

        if (empty($paper)) {
            return $this->initPaper($user_id);
        }

        return $paper;
    }

    /**
     * 获得问题集
     * @param $user_id
     */
    public function getQuestionCollection($user_id)
    {
        $paper = $this->getPaper($user_id);
        $collect_id_arr = json_decode($paper->wait_question_collection_ids, true);
        if (empty($collect_id_arr)) {
            return null;
        }
        $collect_id = $collect_id_arr[0];

    }
}