<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserAnswer extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 未完成
    const STATUS_UNFINISH = 1;
    // 已完成
    const STATUS_FINISH = 2;
    // 已放弃
    const STATUS_GIVEUP = 3;

    // 表名
    protected $table = "user_answers";

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
     * 需要隐藏的字段
     *
     * @var array
     */
    protected $hidden = ['wait_question_collection_ids'];

    /**
     * 应该被转换成原生类型的属性。
     *
     * @var array
     */
    protected $casts = [
        'wait_question_collection_ids' => 'array',
        'data' => 'array',
    ];

    /**
     * 初始化试卷
     * @param $user_id
     * @return $this|Model
     */
    public function initPaper($user_id)
    {
        // 初始化问题
        $collec = QuestionCollection::where('type', QuestionCollection::TYPE_INIT)->firstOrFail();

        return $this->create([
            'user_id' => $user_id,
            'wait_question_collection_ids' => [$collec->id],
        ]);
    }

    /**
     * 获得问题集
     * @param $user_id
     * @return array|Model|static
     */
    public function getQuestionCollection($paper_id, $user_id)
    {
        // 获得试卷
        $paper = $this->where([
            'id' => $paper_id,
            'user_id' => $user_id,
            'stat' => self::STATUS_UNFINISH,
        ])->orderByDesc('created_at')->firstOrFail();
        // 取得问题集
        $collect_id_arr = $paper->wait_question_collection_ids;
        if (empty($collect_id_arr)) {
            // 修改试卷状态为已完成
            $paper->stat = self::STATUS_FINISH;
            $paper->save();

            return [
                'paper_stat' => self::STATUS_FINISH,
                'paper_id' => $paper_id
            ];
        }
        $collect_id = $collect_id_arr[0];
        $collect    = QuestionCollection::where('id', $collect_id)->firstOrFail();

        $questions = $collect->questions()
            ->orderBy('sort')
            ->orderBy('id')
            ->with('questionOption')
            ->get()->toArray();
        // 问题选项增加ABCD
        $letters = range('A', 'Z');
        foreach ($questions as $k => $q) {
            foreach ($q['question_option'] as $kk => $v) {
                $questions[$k]['question_option'][$kk]['lab'] = $letters[$kk];
            }
        }

        $collect['paper_id']   = $paper->id;
        $collect['paper_stat'] = $paper->stat;
        $collect['questions']  = $questions;

        return $collect;
    }

    /**
     * 保存答案
     * @param Request $request
     * @throws \Exception
     */
    public function saveAnswer(Request $request)
    {
        $paper_id               = $request->input('paper_id');
        $data                   = $request->input('data');
        $question_collection_id = $request->input('question_collection_id');

        // 开启事务
        DB::beginTransaction();

        // 验证试卷id是否正确
        Log::debug("提交问题的paper_id");
        Log::debug($paper_id);
        $paper = $this->where([
            'id' => $paper_id,
            'stat' => self::STATUS_UNFINISH,
        ])->firstOrFail();

        // 记录答案
        $oldData     = $paper->data ? $paper->data : [];
        $newData     = [
            'question_collection_id' => $question_collection_id,
            'answer' => $data,
        ];
        $oldData[]   = $newData;
        $paper->data = $oldData;
        // 删除待回答问题
        $oldQuestion = $paper->wait_question_collection_ids;
        if ($oldQuestion[0] != $question_collection_id) {
            DB::rollBack();
            throw new \Exception("删除待回答问题不匹配");
        }
        array_shift($oldQuestion);
        $paper->wait_question_collection_ids = $oldQuestion;
        // 判断是否有分支问题集
        $option_ids = [];
        foreach ($data as $v) {
            $option_ids = array_merge($option_ids, ($v['option_id'] ?? []));
        }
        // 分支问题集id
        if (!empty($option_ids)) {
            $add_collection_id = QuesOpQuesCollect::whereIn('question_option_id', $option_ids)
                ->get()->pluck('question_collection_id')->all();
            if (!empty($add_collection_id)) {
                $temp = $paper->wait_question_collection_ids;
                foreach ($add_collection_id as $collection_id) {
                    array_unshift($temp, $collection_id);
                }
                $paper->wait_question_collection_ids = $temp;
            }
        }

        // 如果是初始化题集 分析出是情感还是法规类型 填充待回答主线问题集
        $initCollec = QuestionCollection::where('type', QuestionCollection::TYPE_INIT)->firstOrFail();
        if ($initCollec->id == $question_collection_id) {
            $suggest = $this->matchSuggest($initCollec, $data);
            // Log::debug("匹配到的建议类型:");
            // Log::debug($suggest);
            if (empty($suggest)) {
                DB::rollBack();
                throw new \Exception("初始化问题没有匹配到建议类型");
            }
            // 保存试卷类型
            $paper->type = $suggest['type'];
            // 填充情感或者法规类型的主线题集
            $collect_ids = QuestionCollection::where('is_trunk', 1)
                ->where('type', $suggest['type'])
                ->orderBy('sort')
                ->orderByDesc("created_at")
                ->get(['id'])->pluck('id')->all();
            if (empty($collect_ids)) {
                DB::rollBack();
                throw new \Exception("没有找到主线问题集");
            }
            $paper->wait_question_collection_ids = $collect_ids;
        }
        // 保存修改
        if (!$paper->save()) {
            DB::rollBack();
            throw new \Exception("保存数据库失败");
        }
        DB::commit();
    }

    /**
     * 匹配情感建议
     * @param QuestionCollection $qc
     * @param array $answer
     * @return null|array
     */
    public function matchSuggest(QuestionCollection $qc, array $answer)
    {
        $suggests = $qc->suggests()->get()->toArray();
        foreach ($suggests as $k => $v) {
            $rule = json_decode($v['pivot']['suggest_rule'], true);
            if ($this->compareRule($rule, $answer)) {
                // 返回建议
                return $v;
            }
        }
        return null;
    }

    /**
     * 对比答案和规则数组是否相同
     * @param $arr1
     * @param $arr2
     * @return bool
     */
    public function compareRule($arr1, $arr2)
    {
        $arr1Count = count($arr1);
        $arr2Count = count($arr2);
        if ($arr1Count != $arr2Count) {
            return false;
        }

        $count = 0;
        foreach ($arr1 as $k => $v) {
            foreach ($arr2 as $kk => $vv) {
                $opArr1 = is_numeric($v['option_id']) ? [$v['option_id']] : $v['option_id'];
                $opArr2 = is_numeric($vv['option_id']) ? [$vv['option_id']] : $vv['option_id'];
                sort($opArr1);
                sort($opArr2);
                if ($opArr1 == $opArr2 && $vv['question_id'] == $v['question_id']) {
                    $count++;
                }
            }
        }
        if ($count != $arr1Count) {
            return false;
        }

        return true;
    }
}