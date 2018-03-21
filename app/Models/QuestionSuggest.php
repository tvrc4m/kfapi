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
use Illuminate\Support\Facades\DB;

class QuestionSuggest extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "question_suggests";

    protected $fillable = ['question_collection_id', 'title', 'content', 'sort', 'stat', 'type'];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


}