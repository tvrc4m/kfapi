<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-16
 * Time: 上午11:24
 */
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Models\City;

class AreaController extends Controller
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
     * 地理位置列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAreaList(Request $request)
    {
        $list = Province::with('provinceCity')->select(['id', 'name'])->get()->toArray();

        return api_success($list);
    }

}