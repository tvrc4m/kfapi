<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Question extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "questions";

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

    /**
     * 关联选项关系
     */
    public function questionOption()
    {
        return $this->hasMany(\App\Models\QuestionOption::class, 'question_id','id');
    }

    public function questionCollection()
    {
        return $this->belongsTo(\App\Models\QuestionCollection::class, 'question_collection_id', 'id');
    }

    /**
     * 保存问题
     * @param Request $request
     * @param int $id
     * @return bool|mixed
     * @throws \Exception
     */
    public function saveQuestion(Request $request, $id = 0)
    {
        // 问题基础信息
        $baseData = $request->except('options');
        // 选项信息
        $options = $request->only('options');

        // 开启事务
        DB::beginTransaction();
        if (empty($id)) { // 新增
            $question = $this->create($baseData);
            if (!$question) {
                DB::rollBack();
                return false;
            }
        } else { // 更新
            $question = $this->where('id', $id)->firstOrFail();
            if (!$question->update($baseData)) {
                DB::rollBack();
                return false;
            }
            // 删除原来的关键词信息
            $ops = QuestionOption::where('question_id', $question->id)->with('keyword')->get();
            foreach ($ops as $op) {
                $del = $op->keyword()->detach();
                if (!$del) {
                    DB::rollBack();
                    return false;
                }
            }
            // 删除原来的选项信息
            $del = QuestionOption::where('question_id', $question->id)->delete();
            if (!$del) {
                DB::rollBack();
                return false;
            }
        }

        // 保存选项信息
        foreach ($options['options'] as $option) {
            $questionOption = QuestionOption::create([
                'question_id' => $question->id,
                'options' => $option['name'],
                'weight' => intval($option['weight'] ?? 0)
            ]);
            if (!$questionOption) {
                DB::rollBack();
                return false;
            }
            foreach ($option['keyword'] as $keyword_id) {
                // 保存关键词信息
                $keyword = QuestionOptionKeyword::create([
                    'question_option_id' => $questionOption->id,
                    'keyword_id' => $keyword_id,
                ]);
                if (!$keyword) {
                    DB::rollBack();
                    return false;
                }
            }
        }

        DB::commit();
        return $question->id;
    }

    /**
     * 删除问题
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function deleteQuestion($id)
    {
        DB::beginTransaction();
        $ques = $this->where('id', $id)->firstOrFail();
        $collect = $ques->questionCollection()->first();
        $collect->decrement('num', 1);
        $del = $ques->delete();
        if (!$del) {
            DB::rollBack();
            return false;
        }
        $ops = QuestionOption::where('question_id', $id)->with('keyword')->get();
        foreach ($ops as $op) {
            $del = $op->keyword()->detach();
        }
        $del = QuestionOption::where('question_id', $id)->delete();

        DB::commit();
        return true;
    }
}