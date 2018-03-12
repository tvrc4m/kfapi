<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseCate;
use App\Models\CaseFactor;
use App\Models\CaseKeyword;
use App\Models\Cases;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * 获得所有分类
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCate()
    {
        $data = CaseCate::all();
        return api_success($data);
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

        $case = Cases::where('id', $id)->firstOrFail();
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

        $list = Cases::where($where)->select(['name','is_breakup', 'created_at', 'updated_at','case_date'])->paginate();
        return api_success($list);
    }

    /**
     * 查看案例
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOneCase($id)
    {
        $data = Cases::where('id', $id)->firstOrFail();
        return api_success($data);
    }

    /**
     * 搜索关键词
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchKeyword(Request $request)
    {
        $this->validate($request, [
            'case_factor_id' => 'required|numeric',
            'name' => 'required|max:255',
        ],[
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'case_factor_id.required' => '要素id不能为空',
            'case_factor_id.numeric' => '要素id不合法',
        ]);

        $case_factor_id = $request->input('case_factor_id');
        $name = $request->input('name');

        $data = Keyword::where('name', 'like', "%{$name}%")
            ->where('case_factor_id', $case_factor_id)
            ->limit(20)
            ->get();
        
        return api_success($data);
    }

    /**
     * 新增关键词
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createKeyword(Request $request)
    {
        $this->validate($request, [
            'case_factor_id' => 'required|numeric',
            'name' => 'required|max:255',
        ],[
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'case_factor_id.required' => '要素id不能为空',
            'case_factor_id.numeric' => '要素id不合法',
        ]);

        $keyword = Keyword::firstOrCreate($request->all());
        if ($keyword->id) {
            return api_success(['keyword_id' => $keyword->id]);
        }
        return api_error();
    }

    /**
     * 保存案例关键词
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function editKeyword(Request $request)
    {
        $this->validate($request, [
            'case_id' => 'required|numeric',
            'data' => 'array',
            'data.*.case_factor_id' => 'required|numeric',
            'data.*.keyword_id' => 'required|numeric',
        ],[
            'case_id.required' => '案例id不能为空',
            'case_id.numeric' => '案例id不合法',
            'data.array' => '数据格式不对',
        ]);

        $case_id = $request->input('case_id');
        $saveArray = $request->input('data');
        // 开启事务
        DB::beginTransaction();
        // 删除原有关键词
        CaseKeyword::where('case_id', $case_id)->delete();
        // 保存新的关键词
        foreach ($saveArray as $v) {
            $v['case_id'] = $case_id;
            $res = CaseKeyword::create($v);
            if (!$res) {
                DB::rollBack();
            }
        }
        DB::commit();

        return api_success();
    }

    /**
     * 查看案例关键词
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllKeyword(Request $request)
    {
        $case_id = $request->input('case_id');
        $case = Cases::with(['caseKeyword'])->where('id', $case_id)->firstOrFail();

        $caseKeyword = $case->caseKeyword()->with(['factor', 'keyword'])->get();

        $res = [];
        foreach ($caseKeyword as $v) {
            if (!isset($res[$v->case_factor_id])) {
                $res[$v->case_factor_id]['case_factor_name'] = $v->factor->name;
                $res[$v->case_factor_id]['case_factor_id'] = $v->case_factor_id;
                $res[$v->case_factor_id]['keywords'] = [];
            }
            $res[$v->case_factor_id]['keywords'][] = [
                'case_factor_id' => $v->case_factor_id,
                'keyword_id' => $v->keyword_id,
                'keyword_name' => $v->keyword->name,
            ];
        }
        return api_success(array_values($res));
    }

    /**
     * 新增案例要素
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFactor(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255|unique:case_factors',
            'count' => 'required|numeric',
            'weight' => 'numeric',
        ],[
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'name.unique' => '名称已存在',
            'count.required' => '数量不能为空',
            'count.numeric' => '数量必须是数字',
            'weight.numeric' => '权重必须是数字',
        ]);

        $factor = CaseFactor::create($request->all());
        if ($factor) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 更新案例要素
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editFactor($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'count' => 'required|numeric',
            'weight' => 'numeric',
        ],[
            'name.required' => '名称不能为空',
            'name.max' => '名称不能超过255个字符',
            'count.required' => '数量不能为空',
            'count.numeric' => '数量必须是数字',
            'weight.numeric' => '权重必须是数字',
        ]);

        $factor = CaseFactor::where('id', $id)->firstOrFail();
        if ($factor->update($request->all())) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 查看案例要素
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllFactor()
    {
        $data = CaseFactor::all();
        return api_success($data);
    }
}
