<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionOption extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "question_options";

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
     * 关键词的关联关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function keyword()
    {
        return $this->belongsToMany(\App\Models\Keyword::class,
            'question_option_keywords',
            'question_option_id',
            'keyword_id');
    }

    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class, 'question_id', 'id');
    }
}