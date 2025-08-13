<?php

namespace App\Models;

use App\Models\Team;
use App\Models\ChatPeer;
use App\Models\Challenge;
use App\Models\ChallengeResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiUser extends Model  implements Authenticatable
{
    use HasFactory;
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password; // Replace 'password' with the name of the password field in your table
    }

    public function getRememberToken()
    {
        return $this->{$this->getRememberTokenName()};
    }

    public function setRememberToken($value)
    {
        $this->{$this->getRememberTokenName()} = $value;
    }

    public function getRememberTokenName()
    {
        return 'remember_token'; // Replace 'remember_token' with the name of the remember token field in your table
    }

    protected $fillable = [
        'name',
        'phone',
        'email',
        'avatar',
        'password',
        'contacts'
    ];


    public function ChatPeeUser()
    {
        return $this->belongsToMany(ChatPeer::class, 'chatpeer_apiuser', 'chat_peer_id', 'participants_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function challenges()
    {
        return $this->belongsToMany(Challenge::class, 'challenges_api_users', 'users_id', 'challenge_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_users', 'user_id', 'team_id');
    }

    public function challengeInvitations()
    {
        return $this->morphMany(ChallengeInvitation::class, 'model');
    }

    public function ownedChatGroups()
    {
        return $this->hasMany(ChatGroup::class, 'created_by');
    }

    public function participatedChatGroups()
    {
        return $this->belongsToMany(ChatGroup::class, 'chat_group_api_user', 'api_user_id', 'chat_group_id')
            ->withPivot('role');
    }

    public function storeOrders()
    {
        return $this->hasMany(StoreOrder::class, 'api_user_id');
    }
}
