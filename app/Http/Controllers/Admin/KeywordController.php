<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseFactor;
use App\Models\Keyword;
use Illuminate\Http\Request;
use App\Models\Law;
use Illuminate\Support\Facades\DB;

class KeywordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }



    /**
     * 要素列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFactorList()
    {
        $list = CaseFactor::select(['id', 'name'])->get();

        return api_success($list);
    }

    /**
     * 关键字列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKeywordList($id)
    {
        $where = [];
        $where['case_factor_id'] = intval($id);
        $list = Keyword::where($where)->select(['id', 'name'])->get();

        return api_success($list);
    }


}
