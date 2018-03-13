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
    public function questionCollectionQuestionOption()
    {
        return $this->hasMany(\App\Models\QuesOpQuesCollect::class, 'question_collection_id', 'id');
        return $this->belongsToMany(Qu, 'role_user', 'user_id', 'role_id');

    }
}