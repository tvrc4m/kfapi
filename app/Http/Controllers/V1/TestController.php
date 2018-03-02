<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;

class TestController extends Controller
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
     * 测试展示
     */
    public function index()
    {
        return api_success();
    }
}
