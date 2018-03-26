<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-13
 * Time: 下午2:58
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class QuestionCollection extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 类型
    const TYPE_LAW = 1;
    const TYPE_EMOTION = 2;
    const TYPE_INIT = 3;
    // 类型名字
    const TYPE_NAME = [
        self::TYPE_LAW => '法律',
        self::TYPE_EMOTION => '情感',
        self::TYPE_INIT => '初始问题',
    ];

    // 表名
    protected $table = "question_collections";

    protected $fillable = ['type', 'title', 'content', 'is_single_page', 'bgimage', 'is_trunk', 'overdue', 'stat',
                            'sort', 'num', 'create_user_id'];


    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 问题集与答案选项关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questionOption()
    {
        return $this->belongsToMany(\App\Models\QuestionOption::class, 'question_option_question_collections', 'question_collection_id',
            'question_option_id');

    }

    /**
     * 问题选项与问题关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class, 'question_id', 'id');
    }

    /**
     * 问题集与后台管理人关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adminUser()
    {
        return $this->belongsTo(\App\Models\AdminUser::class, 'create_user_id', 'id');

    }

    /**
     * 保存问题集
     * @param Request $request
     * @param int $id
     * @return bool|mixed
     * @throws \Exception
     */
    public function saveQuestionCollection(Request $request, $id = 0)
    {
        $data = $request->except('question_option_id');
        $question_option_id = $request->only('question_option_id');
        $type = $request->only('type');
        //开启事务
        $data['create_user_id'] = Auth::guard("admin")->user()->id;
        DB::beginTransaction();
        if (empty($id)){
            if (3==intval($type)){
                $quesType = $this->where('type', $type)->firstOrFail();
                if ($quesType->id && !$quesType->delete() && !QuesOpQuesCollect::where('question_collection_id', $quesType->id)->delete()){
                    DB::rollBack();
                    return false;
                }
            }
            $result = $this->create($data);
            if ($result) {
                if (!empty($question_option_id)){
                    foreach ($question_option_id['question_option_id'] as $key=>$val){
                        $result2 = QuesOpQuesCollect::create(['question_collection_id' => $result->id, 'question_option_id'=> $val]);
                        if (!$result2){
                            DB::rollBack();
                            return false;
                        }
                    }
                }
            }
        }else{
            $quesCollect = $this->where('id', $id)->firstOrFail();
            $result = $quesCollect->update($data);
            if ($result) {
                if (!empty($question_option_id)){
                    $quesOpList = QuesOpQuesCollect::where('question_collection_id', $id)->get()->toArray();
                    if ($quesOpList && !QuesOpQuesCollect::where('question_collection_id', $id)->forceDelete()) {
                        DB::rollBack();
                        return false;
                    }
                    foreach ($question_option_id['question_option_id'] as $key=>$val){
                        $result2 = QuesOpQuesCollect::create(['question_collection_id' => $id, 'question_option_id'=> $val]);
                        if (!$result2){
                            DB::rollBack();
                            return false;
                        }
                    }
                }
            }
        }
        DB::commit();
        return true;
    }

    /**
     * 问题关联关系
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class, 'question_collection_id', 'id');
    }

    /**
     * 建议关联关系
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function suggests()
    {
        return $this->belongsToMany(
            \App\Models\QuestionSuggest::class,
            'question_collection_question_suggests',
            'question_collection_id',
            'question_suggest_id'
        )->withPivot('suggest_rule');
    }

    /**
     * 获取背景图片 自动拼接为url访问地址
     *
     * @param  string  $value
     * @return string
     */
    public function getBgimageAttribute($value)
    {
        return url($value);
    }

    /**
     * 设置背景图片为相对路径
     *
     * @param  string  $value
     * @return string
     */
    public function setBgimageAttribute($value)
    {
        if (strpos($value, "http") === 0) {
            $host = parse_url($value, PHP_URL_HOST);
            if (strtoupper($host) == strtoupper($_SERVER['HTTP_HOST'])) {
                $this->attributes['bgimage'] = parse_url($value, PHP_URL_PATH);
                return;
            }
        }
        $this->attributes['bgimage'] = $value;
    }
}