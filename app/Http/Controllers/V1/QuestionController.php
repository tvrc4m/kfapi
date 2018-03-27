<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\UserAnswer;
use App\Models\UserQuestionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * 问题控制器
 * @package App\Http\Controllers\V1
 */
class QuestionController extends Controller
{
    private $user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * 开始答题 获得试卷id
     */
    public function begin()
    {
        $model = new UserAnswer();
        $paper = $model->initPaper($this->user->id);

        return api_success($paper);
    }

    /**
     * 获得题目
     * @return \Illuminate\Http\JsonResponse
     */
    public function question(Request $request)
    {
        $this->validate($request, [
            'paper_id' => 'required|numeric',
        ], [
            'paper_id.required' => '试卷id不能为空',
            'paper_id.numeric' => '试卷id必须是数字',
        ]);

        $paper_id = $request->input('paper_id');

        // 返回题集题集
        $model = new UserAnswer();
        $collect = $model->getQuestionCollection($paper_id, $this->user->id);

        return api_success($collect);
    }

    /**
     * 提交答案
     */
    public function answer(Request $request)
    {
        // Log::debug($request->all());
        // Log::debug($request->header());
        $this->validate($request, [
            'paper_id' => 'required|numeric',
            'question_collection_id' => 'required|numeric',
            'data' => 'required|array',
            'data.*.type' => 'required',
            'data.*.question_id' => 'required|numeric',
            'data.*.option_id' => 'array',
            'data.*.date' => 'string',
            'data.*.province' => 'numeric',
            'data.*.city' => 'numeric',
        ], [
            'paper_id.required' => '试卷id不能为空',
            'paper_id.numeric' => '试卷id必须是数字',
            'question_collection_id.required' => '问题集id不能为空',
            'question_collection_id.numeric' => '问题集id必须是数字',
            'data.required' => '答案数据不能为空',
            'data.array' => '答案数据必须是数组',
            'data.*.type.required' => '问题类型不能为空',
            'data.*.question_id.required' => '问题id不能为空',
            'data.*.question_id.numeric' => '问题id必须是数字',
            'data.*.option_id.array' => '答案id必须是数组',
        ]);

        // 保存答案
        $model = new UserAnswer();
        try {
            $model->saveAnswer($request);
        } catch (\Exception $e) {
            return api_error($e->getMessage());
        }
        // 返回下一部分题集
        return $this->question($request);
    }

    /**
     * 生成报告书
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function makeReport(Request $request)
    {
        // Log::debug("生成报告书");
        // Log::debug($request->all());
        $this->validate($request, [
            'paper_id' => 'required|numeric|min:1',
        ], [
            'paper_id.required' => '试卷id不能为空',
            'paper_id.numeric' => '试卷id必须是数字',
            'paper_id.min' => '试卷id必须大于0'
        ]);
        $paper_id = $request->input('paper_id');
        $paper = UserAnswer::where([
            'id' => $paper_id,
            'user_id' => $this->user->id,
            'stat' => UserAnswer::STATUS_FINISH,
        ])->firstOrFail();

        // 生成报告书
        $report = new UserQuestionReport();
        $report_id = $report->makeReport($paper);

        if ($report_id === false) {
            return api_error('生成结果失败');
        }

        return api_success(['report_id' => $report_id]);
    }
}
