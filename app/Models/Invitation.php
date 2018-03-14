<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{

    // 表名
    protected $table = "invitations";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];


}