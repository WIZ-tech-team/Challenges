<?php

namespace App\Models;

use App\Models\ApiUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatPeer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'created_by',
        'type',
        'chat_id',
        'lastmessageId',
    ];
    public function participants()
    {
        return $this->belongsToMany(ApiUser::class, 'chatpeer_apiuser', 'chat_peer_id', 'participants_id');
    }
}
