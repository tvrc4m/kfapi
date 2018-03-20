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
        $user_id = \Auth::user()['id'];
        //dd($user_id);
        $this->validate($request, [
            'topic_id' => 'required|numeric',
            'expert_id' => 'required|numeric',
        ],[
            'topic_id.required' => '问题id不能为空',
            'topic_id.numeric' => '问题id不合法',
            'expert_id.required' => '专家id不能为空',
            'expert_id.numeric' => '专家id不合法',
        ]);
        $data = array(
            'user_id'=>$user_id,
            'topic_id'=>$request->input('topic_id'),
            'expert_id'=>$request->input('expert_id'),
        );
        $result = Invitation::create($data);
        if ($result) {
            return api_success();
        }
        return api_error();
    }
}