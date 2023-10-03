<?php

namespace App\Models;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class footballcylic extends Model
{
    use HasFactory;
     protected $fillable =[
        'challenge_id',
        'team_id',
        'cylicNum',
        'result',
        'winner_team',
     ];

     public function teams()
{
    return $this->belongsToMany(Team::class,'footballcylic_team','cylic_id','team_id');
}
}
