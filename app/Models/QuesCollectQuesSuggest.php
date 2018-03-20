<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-15
 * Time: 下午3:34
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\Keyword;
use Illuminate\Support\Facades\DB;

class QuesCollectQuesSuggest extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "question_collection_question_suggests";

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
     * 应该被转换成原生类型的属性。
     *
     * @var array
     */
    protected $casts = [
        'suggest_rule' => 'array',
    ];
}