<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-20
 * Time: 下午2:29
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 问题控制器
 * @package App\Http\Controllers\Admin
 */
class UploadController extends Controller
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
     * 上传图片
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function image(Request $request)
    {
        if (!$request->hasFile('file')) {
            return api_error('图片不存在!');
        }

        $file = $request->file('file');
        if (!$file->isValid()){
            return api_error('图片验证失败!');
        }
        $fileName = md5(microtime()). mt_rand(1000,9999) .'.'.$file->extension();
        $image_url = '/upload/'.date("Y-m-d");
        $dirname = base_path('public').$image_url;
        $data = $file->move($dirname, $fileName);
        if($data){
            return api_success(['image_url'=>$image_url . '/' .$fileName]);
        }

        return api_error('图片上传失败!');

    }

}
