<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunningResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'team_id',
        'user_id',
        'steps',
        'distance',
        'duration',
        'rank',
        'award_id',
        'points'
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(ApiUser::class);
    }

    public function award()
    {
        return $this->belongsTo(Award::class);
    }
}
