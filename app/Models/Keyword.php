<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Keyword extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "keywords";

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
     * 隐藏属性
     *
     * @var array
     */
    protected $hidden = ['stat', 'created_at', 'updated_at', 'deleted_at'];
}