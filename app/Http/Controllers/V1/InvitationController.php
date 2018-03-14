<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invitation;
use Illuminate\Support\Facades\DB;

class InvitationController extends Controller
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

    //用户邀请
    public function addInvitation(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|numeric',
            'topic_id' => 'required|numeric',
            'expert_id' => 'required|numeric',
        ],[
            'user_id.required' => '用户ID不能为空',
            'user_id.numeric' => '用户ID不合法',
            'topic_id.required' => '问题id不能为空',
            'topic_id.numeric' => '问题id不合法',
            'expert_id.required' => '专家id不能为空',
            'expert_id.numeric' => '专家id不合法',
        ]);

        $result = Invitation::create($request->all());
        if ($result) {
            return api_success();
        }
        return api_error();
    }
}