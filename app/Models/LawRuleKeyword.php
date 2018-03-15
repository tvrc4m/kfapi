<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-14
 * Time: 下午4:53
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LawRuleKeyword extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "law_rule_keywords";

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


}