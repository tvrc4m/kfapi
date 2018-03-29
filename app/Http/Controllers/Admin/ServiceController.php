<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use App\Models\Services;
use Illuminate\Http\Request;

class ServiceController extends Controller
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

    //服务列表
    public function getAllService(Request $request)
    {
        $cate = $request->input('cate');
        $services = Services::paginate(20)->where('cate',$cate)->toArray();
        return api_success($services);
    }

    /**
     * 查看服务
     * @param $id
     */
    public function getOneService($id)
    {
        $data = Services::where('id', $id)->firstOrFail();
        return api_success($data);
    }

    /**
     * 新增服务
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addService(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'cate' => 'required|numeric',
        ],[
            'name.required' => '服务名不能为空',
            'name.max' => '服务名不能超过50个字符',
            'cate.required' => '服务类型不能为空',
            'cate.numeric' => '服务类型不合法',
        ]);

        $createService = Services::create($request->all());
//dd($createService);
        if(!$createService){
            return api_error();
        }
        return api_success($createService->toArray());
    }

    /**
     * 删除服务
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteService($id)
    {
        if (Services::destroy(intval($id))) {
            return api_success();
        }
        return api_error();
    }

    /**
     * 修改服务
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editService($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'cate' => 'required|max:255',
        ],[
            'name.required' => '服务名不能为空',
            'name.max' => '服务名不能超过50个字符',
            'cate.required' => '服务类型不能为空',
            'cate.numeric' => '服务类型不合法',
        ]);

        $createService = Services::where('id',$id)->first();
        $res = $createService->update($request->all());
        if(!$res){
            return api_error();
        }
        return api_success();
    }

}
