<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    //添加法规
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

        $result = Laws::create($request->all());
        if ($result) {
            return api_success();
        }
        return api_error();
    }
}
