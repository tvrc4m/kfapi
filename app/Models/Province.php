<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-16
 * Time: 上午11:31
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "provinces";

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
     * 省和市选项关系
     */
    public function provinceCity()
    {
        return $this->hasMany(\App\Models\City::class, 'provinceid','id');
    }

}
