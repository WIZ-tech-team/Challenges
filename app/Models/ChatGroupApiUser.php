<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroupApiUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_group_id',
        'api_user_id',
        'role'
    ];

    public function chatGroup()
    {
        return $this->belongsTo(ChatGroup::class, 'chat_group_id');
    }

    public function apiUser()
    {
        return $this->belongsTo(ApiUser::class, 'api_user_id');
    }
}
