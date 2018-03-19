<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\UserAnswer;
use App\Models\UserQuestionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * 获得题目
     * @return \Illuminate\Http\JsonResponse
     */
    public function question()
    {
        // 返回题集题集
        $model = new UserAnswer();
        $collect = $model->getQuestionCollection($this->user->id);

        return api_success($collect);
    }

    /**
     * 提交答案
     */
    public function answer(Request $request)
    {
        $this->validate($request, [
            'paper_id' => 'required|numeric',
            'question_collection_id' => 'required|numeric',
            'data' => 'required|array',
            'data.*.question_id' => 'required|numeric',
            'data.*.option_id' => 'required|array',
        ], [
            'paper_id.required' => '试卷id不能为空',
            'paper_id.numeric' => '试卷id必须是数字',
            'question_collection_id.required' => '问题集id不能为空',
            'question_collection_id.numeric' => '问题集id必须是数字',
            'data.required' => '答案数据不能为空',
            'data.array' => '答案数据必须是数组',
            'data.*.question_id.required' => '问题id不能为空',
            'data.*.question_id.numeric' => '问题id必须是数字',
            'data.*.option_id.required' => '答案id不能为空',
            'data.*.option_id.array' => '答案id必须是数组',
        ]);

        // 保存答案
        $model = new UserAnswer();
        if (!$model->saveAnswer($request)) {
            return api_error('保存问题失败');
        }
        // 返回下一部分题集
        return $this->getQuestion();
    }

    /**
     * 生成报告书
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function makeReport(Request $request)
    {
        $this->validate($request, [
            'paper_id' => 'required|numeric',
        ], [
            'paper_id.required' => '试卷id不能为空',
            'paper_id.numeric' => '试卷id必须是数字',
        ]);
        $paper = UserAnswer::where('id', $request->input('paper_id'))->firstOrFail();

        // 生成报告书
        $report = new UserQuestionReport();
        $res = $report->makeReport($paper);

        if ($res === false) {
            return api_error('生成结果失败');
        }

        return api_success();
    }

    /**
     * 查看报告书
     * @param Request $request
     */
    public function getReport(Request $request)
    {
        $this->validate($request, [
            'report_id' => 'required|numeric',
        ], [
            'report_id.required' => '报告书id不能为空',
            'report_id.numeric' => '报告书id必须是数字',
        ]);
        // $report_id = $request->input('report_id');
    }
}
