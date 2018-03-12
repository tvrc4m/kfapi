<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use Illuminate\Http\Request;

/**
 * 案例控制器
 * @package App\Http\Controllers\Admin
 */
class CaseController extends Controller
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
     * 创建案例
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCase(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'case_cate_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'case_date' => 'required',
            'info' => 'required',
            'judgment' => 'required',
            'suggest' => 'required',
        ],[
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'case_cate_id.required' => '分类不能为空',
            'case_cate_id.numeric' => '分类不合法',
            'province_id.required' => '省份不能为空',
            'province_id.numeric' => '省份不合法',
            'city_id.required' => '城市不能为空',
            'city_id.numeric' => '城市不合法',
            'case_date.required' => '日期不能为空',
            'info.required' => '案情不能为空',
            'judgment.required' => '判决不能为空',
            'suggest.required' => '建议不能为空',
        ]);

        $case = Cases::create($request->all());
        if ($case) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 删除案例
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCase($id)
    {
        if (Cases::destroy(intval($id))) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 修改案例
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editCase($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'case_cate_id' => 'required|numeric',
            'province_id' => 'required|numeric',
            'city_id' => 'required|numeric',
            'case_date' => 'required',
            'info' => 'required',
            'judgment' => 'required',
            'suggest' => 'required',
        ],[
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'case_cate_id.required' => '分类不能为空',
            'case_cate_id.numeric' => '分类不合法',
            'province_id.required' => '省份不能为空',
            'province_id.numeric' => '省份不合法',
            'city_id.required' => '城市不能为空',
            'city_id.numeric' => '城市不合法',
            'case_date.required' => '日期不能为空',
            'info.required' => '案情不能为空',
            'judgment.required' => '判决不能为空',
            'suggest.required' => '建议不能为空',
        ]);

        $case = Cases::first($id);
        if ($case->update($request->all())) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 案例列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCase(Request $request)
    {
        $case_cate_id = $request->input('case_cate_id');
        $is_breakup = $request->input('is_breakup');
        $case_date = $request->input('case_date');

        $where = [];
        if (!empty($case_cate_id)) {
            $where['case_cate_id'] = $case_cate_id;
        }
        if (!empty($is_breakup)) {
            $where['is_breakup'] = $is_breakup;
        }
        if (!empty($case_date)) {
            $where['case_date'] = $case_date;
        }

        $list = Cases::where($where)->paginate();
        return api_success($list);
    }

    /**
     * 查看案例
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneCase($id)
    {
        $data = Cases::where('id', $id)->first();
        return api_success($data);
    }
}
