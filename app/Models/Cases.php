<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cases extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "cases";

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
    protected $dates = ['deleted_at', 'case_date'];

    /**
     * 关键词关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function caseKeyword()
    {
        return $this->hasMany(\App\Models\CaseKeyword::class, 'case_id', 'id');
    }
}