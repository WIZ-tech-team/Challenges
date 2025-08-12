<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'avatar',
        'created_by',
        'status'
    ];

    public function creator()
    {
        return $this->belongsTo(ApiUser::class, 'created_by');
    }

    public function apiUsers()
    {
        return $this->belongsToMany(ApiUser::class, 'chat_group_api_user', 'chat_group_id', 'api_user_id')
            ->withPivot('role');
    }
}
