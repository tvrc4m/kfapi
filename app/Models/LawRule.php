<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\Keyword;
use Illuminate\Support\Facades\DB;

class LawRule extends Model
{
    // 载入软删除方法
    use SoftDeletes;

    // 表名
    protected $table = "law_rules";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 法规条目与匹配次关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lawRuleKeyword()
    {
        //return $this->hasMany(\App\Models\LawRuleKeyword::class, 'law_rule_id', 'id');
        return $this->belongsToMany(\App\Models\Keyword::class, 'law_rule_keywords', 'law_rule_id',
            'keyword_id');
    }

    /**
     * 保存法规条目信息
     * @param Request $request
     * @param int $id
     * @return bool|mixed
     * @throws \Exception
     */
    public function saveLawRule(Request $request, $id = 0)
    {
        //主信息
        $data = $request->except('keyword');
        //匹配词信息
        $keywords = $request->only('keyword');
        //开启事务
        DB::beginTransaction();
        if (empty($id)){
            $result = LawRule::create($data);
            if ($result) {
                if (!empty($keywords)){
                    foreach ($keywords['keyword'] as $key=>$val){
                        $result2 = LawRuleKeyword::create(['law_rule_id' => $result->id, 'keyword_id'=> $val]);
                        if (!$result2){
                            DB::rollBack();
                            return false;
                        }
                    }
                }
            }
        }else{
            $lawRule = LawRule::where('id', $id)->firstOrFail();
            $result = $lawRule->update($data);
            if ($result) {
                if (!empty($keywords)){
                    if (!LawRuleKeyword::where('law_rule_id', $id)->delete()) {
                        DB::rollBack();
                        return false;
                    }
                    foreach ($keywords['keyword'] as $key=>$val){
                        $result2 = LawRuleKeyword::create(['law_rule_id' => $id, 'keyword_id'=> $val]);
                        if (!$result2){
                            DB::rollBack();
                            return false;
                        }
                    }
                }
            }
        }
        DB::commit();
        return true;
    }
}