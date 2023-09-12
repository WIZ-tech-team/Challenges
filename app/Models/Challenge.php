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
        'team_id',
        'refree_id',
        'category_id',
        'start_time',
        'end_time'  ,
        'date',
        'distance',
        'stepsNum',
        'opponent_id',
        'prize'
        
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

}
