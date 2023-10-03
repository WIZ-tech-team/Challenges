<?php

namespace App\Models;

use App\Models\Team;
use App\Models\ApiUser;
use App\Models\Challenge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChallengeResult extends Model
{
    use HasFactory;
    protected $fillable=[
'result_data'
    ];
   

    public function challenge() {
        return $this->belongsTo(Challenge::class);
    }
    public function user() {
        return $this->belongsTo(ApiUser::class,'user_id');
    }

    public function team() {
        return $this->belongsTo(Team::class,'team_id');
    }
    public function opponent() {
        return $this->belongsTo(Team::class,'opponent_result');
    }
}
