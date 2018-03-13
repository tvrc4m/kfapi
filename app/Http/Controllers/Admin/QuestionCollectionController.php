<?php
/**
 * Created by PhpStorm.
 * User: xay
 * Date: 18-3-13
 * Time: 下午2:39
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\QuestionCollection;
use App\Models\QuesOpQuesCollect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 问题集控制器
 * @package App\Http\Controllers\Admin
 */
class QuestionCollectionController extends Controller
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
     * 新增问题集
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'type' => 'required',
            'is_trunk' => 'required',
            'title' => 'required|max:255',
            'content' => 'required|max:255',
            'is_single_page' => 'required',
            'overdue' => 'required|max:255',
            'question_option_id' => 'required|max:255',
        ],[
            'type.required' => '类型不能为空',
            'is_trunk.required' => '分支不能为空',
            'title.required' => '标题不能为空',
            'title.max' => '标题不能超过255个字符',
            'content.required' => '标题不能为空',
            'content.max' => '标题不能超过255个字符',
            'is_single_page.required' => '不能为空',
            'overdue.required' => '标题不能为空',
            'overdue.max' => '标题不能超过255个字符',
            'question_option_id.required' => '前置问题集ID不能为空',
            'question_option_id.max' => '前置问题集ID不能超过255个字符',
        ]);
        //开启事务
        $question_option_id = $request->input('question_option_id');
        $data = $request->except('question_option_id');
        $data['create_user_id'] = Auth::guard("admin")->user()->id;
        DB::beginTransaction();
        $result = QuestionCollection::create($data);
        if ($result) {
            if (!empty($question_option_id)){
                foreach ($question_option_id as $key=>$val){
                    $result2 = QuesOpQuesCollect::create(['question_collection_id' => $result->id, 'question_option_id'=> $val]);
                    if (!$result2){
                        DB::rollBack();
                        return api_error();
                    }
                }
            }
        }
        DB::commit();
        return api_success();
    }

    /**
     * 问题集列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|numeric',
        ],[
            'type.required' => '法规不能为空',
            'type.numeric' => '法规不合法',
        ]);

        $type = $request->input('type');

        $where = [];
        if (!empty($type)) {
            $where['type'] = intval($type);
        }
        $list = QuestionCollection::where($where)->select(['id','title', 'content', 'is_single_page', 'bgimage', 'is_trunk',
            'type', 'overdue'])->paginate();

        return api_success($list);
    }

    /**
     * 查看某个问题集
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        $data = QuestionCollection::with(['questionCollectionQuestionOption'])->where('id', $id)->firstOrFail();
        return api_success($data);
    }

    /**
     * 问题集编辑
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id, Request $request)
    {
        $this->validate($request, [
            'law_id' => 'required|numeric',
            'title' => 'required|max:255',
            'content' => 'required|max:255',
            'data' => 'array',
            'data.*.case_factor_id' => 'required|numeric',
            'data.*.keyword_id' => 'required|numeric',
        ],[
            'law_id.required' => '法规不能为空',
            'law_id.numeric' => '法规不合法',
            'title.required' => '法规条目名称不能为空',
            'title.max' => '法规条目名称不能超过255个字符',
            'content.required' => '内容不能为空',
            'content.max' => '内容不能超过255个字符',
            'data.array' => '数据格式不对',
        ]);

        $lawRule = LawRule::where('id', $id)->firstOrFail();
        if ($lawRule->update($request->all())) {
            return api_success();
        }
        return api_error();
    }
}