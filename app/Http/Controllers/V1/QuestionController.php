<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\UserAnswer;
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
     * 开始答题
     */
    public function begin()
    {
        // 创建考卷
        $answer = (new UserAnswer())->initAns($this->user->id);
        // 返回题集题集

    }

    public function getCollection(Request $request)
    {
        // UserAnswer::where('id', )->first();
    }
}
