<?php

namespace App\Models;

use App\Models\ApiUser;
use App\Models\Challenge;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'image',
        'user_id',
        'category'
    ];

    public function apiUsers()
    {
        return $this->hasMany(ApiUser::class ,'team_id','id');
    }
    public function invitation()
    {
        return $this->hasMany(Invitation::class ,'team_id','id');
    }

    public function challenges() {
        return $this->hasMany(Challenge::class);
    }
}
