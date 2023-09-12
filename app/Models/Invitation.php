<?php

namespace App\Models;

use App\Models\Team;
use App\Models\ApiUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invitation extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'team_id',
        'status'];

        public function team()
        {
            return $this->belongsTo(Team::class, 'team_id','id');
        }   
        public function user()
        {
            return $this->belongsTo(ApiUser::class);
        } }
        
