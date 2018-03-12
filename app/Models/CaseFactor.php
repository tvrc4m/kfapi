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
}