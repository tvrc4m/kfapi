<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseCate;
use Illuminate\Http\Request;

/**
 * 案例分类控制器
 * @package App\Http\Controllers\Admin
 */
class CaseCateController extends Controller
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
     * 新增案例分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ], [
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
        ]);

        $cate = CaseCate::create($request->all());
        if ($cate) {
            return api_success($cate);
        }

        return api_error();
    }

    /**
     * 编辑案例分类
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
        ], [
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
        ]);
        $cate = CaseCate::where('id', $id)->firstOrFail();
        $res = $cate->update($request->all());
        if ($res) {
            return api_success($cate);
        }

        return api_error();
    }

    /**
     * 删除案例分类
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete($id)
    {
        $cate = CaseCate::where('id', $id)->firstOrFail();
        $res = $cate->delete();
        if ($res) {
            return api_success();
        }

        return api_error();
    }

    /**
     * 获得详情
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne($id)
    {
        $cate = CaseCate::where('id', $id)->firstOrFail();

        return api_success($cate);
    }

    /**
     * 获得所有分类
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $data = CaseCate::all();

        return api_success($data);
    }
}
