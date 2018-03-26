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

    /**
     * 获得所有关键字列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $data = CaseFactor::with('keywords')->get();

        return api_success($data);
    }

    /**
     * 修改关键词
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ],[
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
        ]);

        $key = Keyword::where('id', $id)->firstOrFail();
        if (!$key->update($request->all())) {
            return api_error();
        }
        return api_success($key);
    }

    /**
     * 删除关键词
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        if (!Keyword::destroy($id)) {
            return api_error();
        }
        return api_success();
    }
}
