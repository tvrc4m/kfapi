<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertsServices extends Model
{
    // 表名
    protected $table = "experts_services";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
}