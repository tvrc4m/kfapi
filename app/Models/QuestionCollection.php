<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-13
 * Time: 下午2:58
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionCollection extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "question_collections";

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
     * 问题集与答案选项关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questionOption()
    {
        return $this->belongsToMany(\App\Models\QuestionOption::class, 'question_option_question_collections', 'question_collection_id',
            'question_option_id');

    }

    /**
     * 保存问题集
     * @param Request $request
     * @param int $id
     * @return bool|mixed
     * @throws \Exception
     */
    public function saveQuestionCollecttion(Request $request, $id = 0)
    {
        $data = $request->except('question_option_id');
        $question_option_id = $request->only('question_option_id');
        //开启事务
        $data['create_user_id'] = Auth::guard("admin")->user()->id;
        DB::beginTransaction();
        if (empty($id)){
            $result = QuestionCollection::create($data);
            if ($result) {
                if (!empty($question_option_id)){
                    foreach ($question_option_id as $key=>$val){
                        $result2 = QuesOpQuesCollect::create(['question_collection_id' => $result->id, 'question_option_id'=> $val]);
                        if (!$result2){
                            DB::rollBack();
                            return false;
                        }
                    }
                }
            }
        }else{
            $quesCollect = QuestionCollection::where('id', $id)->firstOrFail();
            $result = $quesCollect->update($data);
            if ($result) {
                if (!empty($question_option_id)){
                    if (!QuesOpQuesCollect::where('question_collection_id', $id)->delete()) {
                        DB::rollBack();
                        return false;
                    }
                    foreach ($question_option_id as $key=>$val){
                        $result2 = QuesOpQuesCollect::create(['question_collection_id' => $id, 'question_option_id'=> $val]);
                        if (!$result2){
                            DB::rollBack();
                            return false;
                        }
                    }
                }
            }
        }
        DB::commit();
        return true;
    }
}