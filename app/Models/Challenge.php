<?php

namespace App\Models;

use App\Models\Team;
use App\Models\ApiUser;
use App\Models\Category;
use App\Models\ChallengeResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Challenge extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'type',
        'latitude',
        'longitude',
        'address',
        'team_id',
        'refree_id',
        'category_id',
        'start_time',
        'end_time'  ,
        'distance',
        'stepsNum',
        'opponent_id',
        'prize',
        'category',
        'image'
        
    ];
    public function users()
    {
        return $this->belongsToMany(ApiUser::class, 'challenges_api_users', 'challenge_id', 'users_id');
    } 
    
    public function results() {
        return $this->hasMany(ChallengeResult::class);
    }

    // app/Models/Challenge.php

public function category()
{
    return $this->belongsTo(Category::class);
}
public function team()
{
    return $this->belongsTo(Team::class);
}
public function opponent()
{
    return $this->belongsTo(Team::class, 'opponent_id');
}

    public function awards()
    {
        return $this->hasMany(Award::class);
    }

    public function footballResults()
    {
        return $this->hasMany(FootballResult::class);
    }

    public function runningResults()
    {
        return $this->hasMany(RunningResult::class);
    }

    public function categoryResults()
    {
        return $this->category === "football" ? $this->footballResults() : 
               ($this->category === "running" ? $this->runningResults() : $this->results());
    }

    public function invitations()
    {
        return $this->hasMany(ChallengeInvitation::class);
    }

    public function participantTeams()
    {
        return $this->belongsToMany(Team::class, 'challenge_team', 'challenge_id', 'team_id');
    }

    public function referee()
    {
        return $this->belongsTo(ApiUser::class, 'refree_id');
    }

}
