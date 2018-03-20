<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseFactor extends Model
{
    // 表名
    protected $table = "case_factors";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 关键词关联关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keywords()
    {
        return $this->hasMany(\App\Models\Keyword::class, 'case_factor_id', 'id');
    }
}