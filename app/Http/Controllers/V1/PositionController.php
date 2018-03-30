<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-29
 * Time: 下午4:53
 */
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Province;
use App\Models\City;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
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

    //根据经纬度获取省和市
    public function setPosition(Request $request)
    {
        $this->validate($request, [
            'position_lat' => 'required|max:255',
            'position_lng' => 'required|max:255',
        ], [
            'position_lat.required' => '经度不能为空',
            'position_lat.max' => '经度不能超过255个字符',
            'position_lng.required' => '纬度不能为空',
            'position_lng.max' => '纬度不能超过255个字符'
        ]);
        $position_lat = $request->input('position_lat');  //纬度
        $position_lng = $request->input('position_lng');

        $ak = config('common.baidu.map.ak.server','teGyPg2ASxm5Y7G9VRmfjZTOimmTKAiE');
        $string = 'http://api.map.baidu.com/geocoder/v2/?callback=renderReverse&location='.$position_lat.','.$position_lng.'&output=xml&pois=1&ak='.$ak;

        $info = file_get_contents($string);
        $position_info = simplexml_load_string($info);
        $position_info = json_decode(json_encode($position_info), true);
        $province = mb_substr($position_info['result']['addressComponent']['province'], 0 ,2);
        $city = mb_substr($position_info['result']['addressComponent']['city'], 0 ,2);
        $district = mb_substr($position_info['result']['addressComponent']['district'], 0 ,2);
        if (!empty($province)){
            $province_info = Province::where('name', 'like', '%'.$province.'%')->get()->toArray();
            if (!empty($province_info)){
                if (in_array($province_info[0]['id'], [11,12,31,50,81,82])){
                    $city = $district;
                }
                $city_info = City::where('provinceid', $province_info[0]['id'])->where('name', 'like', '%'.$city.'%')->get()->toArray();
                if (empty($city_info)){
                    $province_id = '11';
                    $city_id = '1105';
                }else{
                    $province_id = $province_info[0]['id'];
                    $city_id = $city_info[0]['cityid'];
                }
            }else{
                $province_id = '11';
                $city_id = '1105';
            }
        }else{
            $province_id = '11';
            $city_id = '1105';
        }
        //更新
        $user_id = $request->user()->id;
        if (!$user_id){
            return api_error('未找到用户id');
        }
        $result = User::where('id', $user_id)->update(['province_id' => $province_id, 'city_id' => $city_id]);
        if (!$result){
            return api_error();
        }
        return api_success();
    }

}
