<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeInvitation extends Model
{
    use HasFactory;

    public $fillable = [
        'challenge_id',
        'model_type',
        'model_id',
        'status',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}
