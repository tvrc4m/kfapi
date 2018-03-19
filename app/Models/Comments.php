<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{

    // 表名
    protected $table = "comments";

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * 帖子回复关联关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function topic()
    {
        return $this->belongsTo(\App\Models\Topics::class, 'id', 'topic_id');
    }

}