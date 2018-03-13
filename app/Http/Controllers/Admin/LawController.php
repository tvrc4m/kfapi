<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LawRule;
use Illuminate\Http\Request;
use App\Models\Law;
use Illuminate\Support\Facades\DB;

class LawController extends Controller
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
     * 添加法规
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLaw(Request $request)
    {
        $this->validate($request, [
            'fullname' => 'required|max:255',
            'name' => 'required|max:255',
            'pingyin' => 'required|max:255',
        ],[
            'fullname.required' => '法规全称不能为空',
            'fullname.max' => '法规全称不能超过255个字符',
            'name.required' => '法规简称不能为空',
            'name.max' => '法规简称不能超过255个字符',
            'pingyin.required' => '拼音缩写不能为空',
            'pingyin.max' => '拼音缩写不能超过255个字符',
        ]);

        $result = Law::create($request->all());
        if ($result) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 法规列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLaw(Request $request)
    {
        $name = $request->input('name');

        $where = [];
        if (!empty($name)) {
            $where[] = ['name','like', '%'.$name.'%'];
        }
        $list = Law::where($where)->select(['id','fullname', 'name', 'pingyin'])->paginate;

        return api_success($list);
    }

    /**
     * 查看某个法规
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLawDetail($id)
    {
        $data = Law::where('id', $id)->firstOrFail();
        return api_success($data);
    }

    /**
     * 法规编辑
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id, Request $request)
    {
        $this->validate($request, [
            'fullname' => 'required|max:255',
            'name' => 'required|max:255',
            'pingyin' => 'required|max:255',
        ],[
            'fullname.required' => '法规全称不能为空',
            'fullname.max' => '法规全称不能超过255个字符',
            'name.required' => '法规简称不能为空',
            'name.max' => '法规简称不能超过255个字符',
            'pingyin.required' => '拼音缩写不能为空',
            'pingyin.max' => '拼音缩写不能超过255个字符',
        ]);

        $law = Law::where('id', $id)->firstOrFail();
        if ($law->update($request->all())) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 法规删除
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        if (Law::destroy(intval($id))) {
            return api_success();
        }
        return api_error();
    }

    /*********************************************法规条目***************************************************/

    /**
     * 添加法规条目
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addLawRule(Request $request)
    {
        $this->validate($request, [
            'law_id' => 'required|numeric',
            'title' => 'required|max:255',
            'content' => 'required|max:255',
            'data' => 'array',
            'data.*.case_factor_id' => 'required|numeric',
            'data.*.keyword_id' => 'required|numeric',
        ],[
            'law_id.required' => '法规不能为空',
            'law_id.numeric' => '法规不合法',
            'title.required' => '法规条目名称不能为空',
            'title.max' => '法规条目名称不能超过255个字符',
            'content.required' => '内容不能为空',
            'content.max' => '内容不能超过255个字符',
            'data.array' => '数据格式不对',
        ]);

        $result = LawRule::create($request->all());
        if ($result) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 法规条目列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLawRule(Request $request)
    {
        $this->validate($request, [
            'law_id' => 'required|numeric',
        ],[
            'law_id.required' => '法规不能为空',
            'law_id.numeric' => '法规不合法',
        ]);

        $law_id = $request->input('law_id');

        $where = [];
        if (!empty($law_id)) {
            $where['law_id'] = $law_id;
        }
        $list = LawRule::where($where)->select(['id','law_id', 'title', 'content'])->paginate();

        return api_success($list);
    }

    /**
     * 查看某个法规条目
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLawRuleDetail($id)
    {
        $data = LawRule::where('id', $id)->firstOrFail();
        return api_success($data);
    }

    /**
     * 法规条目编辑
     * @return \Illuminate\Http\JsonResponse
     */
    public function editLawRule($id, Request $request)
    {
        $this->validate($request, [
            'law_id' => 'required|numeric',
            'title' => 'required|max:255',
            'content' => 'required|max:255',
            'data' => 'array',
            'data.*.case_factor_id' => 'required|numeric',
            'data.*.keyword_id' => 'required|numeric',
        ],[
            'law_id.required' => '法规不能为空',
            'law_id.numeric' => '法规不合法',
            'title.required' => '法规条目名称不能为空',
            'title.max' => '法规条目名称不能超过255个字符',
            'content.required' => '内容不能为空',
            'content.max' => '内容不能超过255个字符',
            'data.array' => '数据格式不对',
        ]);

        $lawRule = LawRule::where('id', $id)->firstOrFail();
        if ($lawRule->update($request->all())) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 法规条目删除
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLawRule($id)
    {
        if (LawRule::destroy(intval($id))) {
            return api_success();
        }
        return api_error();
    }


}
