<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topics extends Model
{

    // 表名
    protected $table = "topics";

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
    public function topicComment()
    {
        return $this->hasMany(\App\Models\Comment::class, 'topic_id', 'id');
    }
}