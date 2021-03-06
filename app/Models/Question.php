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
        $question_collection_id = $request->input('question_collection_id');

        // 开启事务
        DB::beginTransaction();
        if (empty($id)) { // 新增
            $question = $this->create($baseData);
            QuestionCollection::where('id', $question_collection_id)->increment('num', 1);
            if (!$question) {
                DB::rollBack();
                throw new \Exception('新增失败');
            }
        } else { // 更新
            $question = $this->where('id', $id)->firstOrFail();
            if (!$question->update($baseData)) {
                DB::rollBack();
                throw new \Exception('更新失败');
            }
            /*// 删除原来的关键词信息
            $ops = QuestionOption::where('question_id', $question->id)->with('keyword')->get();
            foreach ($ops as $op) {
                $op->keyword()->detach();
            }
            // 删除原来的选项信息
            QuestionOption::where('question_id', $question->id)->delete();*/
        }

        // 保存选项信息
        if (!empty($options['options'])) {
            foreach ($options['options'] as $option) {
                $questionOption = QuestionOption::updateOrCreate(['question_id' => $question->id,'options' => $option['name']],[
                    'weight' => intval($option['weight'] ?? 0)
                ]);
                if (!$questionOption) {
                    DB::rollBack();
                    throw new \Exception('保存选项信息失败');
                }
                if (!empty($option['keyword'])) {
                    foreach ($option['keyword'] as $keyword_id) {
                        // 保存关键词信息
                        $keyword = QuestionOptionKeyword::updateOrCreate([
                            'question_option_id' => $questionOption->id,
                            'keyword_id' => $keyword_id,
                        ]);
                        if (!$keyword) {
                            DB::rollBack();
                            throw new \Exception('保存关键词信息失败');
                        }
                    }
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
        // 题集数量减一
        $collect->decrement('num', 1);
        // 删除问题
        $del = $ques->delete();
        if (!$del) {
            DB::rollBack();
            return false;
        }
        // 删除选项关联的关键词
        $ops = QuestionOption::where('question_id', $id)->with('keyword')->get();
        foreach ($ops as $op) {
            $op->keyword()->detach();
        }
        // 删除关联选项
        QuestionOption::where('question_id', $id)->delete();
        // 删除建议关联
        $rules = QuesCollectQuesSuggest::where('question_collection_id', $collect->id)->get();
        if (!empty($rules)) {
            foreach ($rules as $v) {
                $oldRule = $v->suggest_rule;
                foreach ($oldRule as $k => $r) {
                    if ($r['question_id'] == $id) {
                        unset($oldRule[$k]);
                    }
                }
                if (empty($oldRule)) {
                    $v->delete();
                } else {
                    $v->suggest_rule = array_values($oldRule);
                    $v->saveOrFail();
                }
            }
        }

        DB::commit();
        return true;
    }
}