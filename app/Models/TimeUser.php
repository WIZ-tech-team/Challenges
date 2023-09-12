<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'team_id',
        'user_id',
        'challenge_id',
        'UserStartTime'
        
       
     ];
}
