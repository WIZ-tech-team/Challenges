<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
