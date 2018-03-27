<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportTemplates;
use Illuminate\Http\Request;

class ReportTemplateController extends Controller
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
     * 查看模板
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOne()
    {
        $temp = ReportTemplates::first();
        return api_success($temp);
    }

    /**
     * 保存模板
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function edit(Request $request)
    {
        $temp = ReportTemplates::first();
        if ($temp->id > 0) {
            $temp->content = $request->input('content');
            $temp->saveOrFail();
        } else {
            ReportTemplates::create([
                'content' => $request->input('content')
            ]);
        }

        return api_success();
    }
}
