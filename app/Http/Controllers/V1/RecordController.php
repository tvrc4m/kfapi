<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Topics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RecordController extends Controller
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
     * 用户评测记录
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllRecord(Request $request)
    {
        $userid = Auth::user()->toArray()['id'];
        //dd($userid);
        $perpage = $request->input('per_page');
        $record = DB::table('topics')
            ->leftJoin('users', 'users.id', '=', 'topics.user_id')
            ->select('topics.cate','topics.content','topics.created_at','users.user_name','users.province_id','users.city_id')
            ->where('topics.user_id',$userid)
            ->paginate($perpage)
            ->toArray();

        $provinces = DB::table('provinces')
            ->select('provinces.id','provinces.name')
            ->get()
            ->toArray();
        //dd($provinces);
        foreach($provinces as $k=>$v){
            $pArr[$v->id] = $v->name;
        }
        $city = DB::table('citys')
            ->select('citys.cityid','citys.name')
            ->get()
            ->toArray();
        foreach($city as $k=>$v){
            $cArr[$v->cityid] = $v->name;
        }
        //dd($cArr);
        if($record['data']){
            foreach ($record['data'] as $k=>&$v){
                //dd($city);
                $v->area = $pArr[$v->province_id].$cArr[$v->city_id];
                if($v->cate == 1){
                    $v->cate = '法律';
                }elseif($v->cate == 2){
                    $v->cate = '情感';
                }else{
                    $v->cate = '';
                }
                unset($v->province_id,$v->city_id);
            }
        }

        //dd($data);
        return api_success($record);
    }

}
