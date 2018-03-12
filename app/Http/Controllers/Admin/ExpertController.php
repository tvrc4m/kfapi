<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;

use App\Models\Experts;
use Illuminate\Http\Request;

class ExpertController extends Controller
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

    //专家列表
    public function getAllExpert()
    {
        $experts = Experts::all()->paginate();
        return api_success($experts);
    }

}
