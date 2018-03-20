<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'wait_question_collection_ids' => json_encode([$collec->id]),
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
        ])->orderByDesc('updated_at')->firstOrFail();
        // 取得问题集
        $collect_id_arr = json_decode($paper->wait_question_collection_ids, true);
        if (empty($collect_id_arr)) {
            // 修改试卷状态为已完成
            $paper->stat = self::STATUS_FINISH;
            $paper->save();

            return ['paper_stat' => self::STATUS_FINISH];
        }
        $collect_id = $collect_id_arr[0];
        $collect = QuestionCollection::where('id', $collect_id)->firstOrFail();

        $questions = $collect->questions()->get()->toArray();
        $collect['paper_id'] = $paper->id;
        $collect['paper_stat'] = $paper->stat;
        $collect['questions'] = $questions;

        return $collect;
    }

    /**
     * 保存答案
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    public function saveAnswer(Request $request)
    {
        $paper_id = $request->input('paper_id');
        $question_collection_id = $request->input('question_collection_id');
        $data = $request->input('data');

        // 开启事务
        DB::beginTransaction();
        // 验证试卷id是否正确
        $paper = $this->where([
            'paper_id' => $paper_id,
            'stat' => self::STATUS_UNFINISH,
        ])->firstOrFail();

        // 记录答案
        $oldData = json_decode(($paper->data ?: []), true);
        $newData = [
            'question_collection_id' => $question_collection_id,
            'answer' => $data,
        ];
        $paper->data = array_merge($oldData, $newData);
        // 删除待回答问题
        $oldQuestion = json_decode($paper->wait_question_collection_ids, true);
        if ($oldQuestion[0] != $question_collection_id) {
            DB::rollBack();
            return false;
        }
        array_shift($oldQuestion);
        $paper->wait_question_collection_ids = json_encode($oldQuestion);

        // 如果是初始化题集 分析出是情感还是法规类型 填充待回答主线问题集
        $initCollec = QuestionCollection::where('type', QuestionCollection::TYPE_INIT)->firstOrFail();
        if ($initCollec->id == $question_collection_id) {
            $suggest = $this->matchSuggest($initCollec, $data);
            if (empty($suggest)) {
                DB::rollBack();
                return false;
            }
            $paper->type = $suggest['type'];
            // 填充情感或者法规类型的主线题集
            $collect_ids = QuestionCollection::where('is_trunk', 1)
                ->where('type', $suggest['type'])
                ->orderBy('sort')
                ->get(['id'])->pluck('id')->all();
            if (empty($collect_ids)) {
                DB::rollBack();
                return false;
            }
            $paper->wait_question_collection_ids = json_encode($collect_ids);
        }
        // 保存修改
        if (!$paper->save()) {
            DB::rollBack();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * 匹配建议
     * @param QuestionCollection $qc
     * @param array $answer
     * @return null|array
     */
    private function matchSuggest(QuestionCollection $qc, array $answer)
    {
        $suggests = $qc->suggests()->get()->toArray();
        foreach ($suggests as $k => $v) {
            $rule = json_decode($v['pivot']['suggest_suggest_rule'], true);
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
                if ($vv['option_id'] == $v['option_id'] && $vv['question_id'] == $v['question_id']) {
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