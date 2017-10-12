<?php

namespace Zhiyi\Component\ZhiyiPlus\PlusComponentIm\Models;

use Illuminate\Database\Eloquent\Model;

class ImConversation extends Model
{
    /**
     * 定义表名.
     *
     * @var string
     */
    protected $table = 'im_conversations';

    /**
     * 定义�
     * �许更新的字段.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'cid', 'name', 'pwd', 'type', 'created_at', 'uids'];

    /**
     * 定义隐藏的字段.
     *
     * @var array
     */
    protected $hidden = ['id', 'is_disabled', 'created_at', 'updated_at'];

    /**
     * 设置保存时将uids字段为逗号分割的字符串.
     *
     * @author martinsun <syh@sunyonghong.com>
     * @datetime 2017-02-04T16:50:26+080
     *
     * @version  1.0
     *
     * @param array|string $uids 以逗号分割的或为数组的数据
     */
    public function setUidsAttribute($uids)
    {
        $uids = is_array($uids) ? $uids : explode(',', $uids);
        $uids = array_unique($uids);
        sort($uids);
        $this->attributes['uids'] = implode(',', $uids);
    }
}
