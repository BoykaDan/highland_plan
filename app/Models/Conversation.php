<?php

namespace Zhiyi\Plus\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['type', 'user_id', 'content', 'options', 'system_mark'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
