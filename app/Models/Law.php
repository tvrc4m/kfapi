<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Law extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "laws";

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

    public function rules()
    {
        return $this->hasMany(\App\Models\LawRule::class, 'law_id', 'id');
    }
}