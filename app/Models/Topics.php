<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{

    // 表名
    protected $table = "topics";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

}