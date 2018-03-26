<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplates extends Model
{
    // 表名
    protected $table = "report_templates";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];
}