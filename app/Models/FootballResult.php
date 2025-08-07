<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FootballResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'team_1_id',
        'team_1_score',
        'team_1_award_id',
        'team_1_points',
        'team_2_id',
        'team_2_score',
        'team_2_award_id',
        'team_2_points'
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team_1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team_2_id');
    }

    public function team1Award()
    {
        return $this->belongsTo(Award::class, 'team_1_award_id');
    }

    public function team2Award()
    {
        return $this->belongsTo(Award::class, 'team_2_award_id');
    }

}
