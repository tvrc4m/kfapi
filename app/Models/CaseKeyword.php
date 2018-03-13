<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseKeyword extends Model
{
    // 表名
    protected $table = "case_keywords";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 关键词关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function keyword()
    {
        return $this->hasOne(\App\Models\Keyword::class, 'id', 'keyword_id');
    }

    /**
     * 要素关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function factor()
    {
        return $this->hasOne(\App\Models\CaseFactor::class, 'id', 'case_factor_id');
    }
}