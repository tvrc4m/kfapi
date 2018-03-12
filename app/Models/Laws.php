<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-12
 * Time: 下午3:55
 */
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laws extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "laws";


}