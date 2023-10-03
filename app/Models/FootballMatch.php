<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FootballMatch extends Model
{
    use HasFactory;
    protected $fillable =[
        'cylic_id',
        'teams',
        'matchNum',
     ];

     public function teams()
{
    return $this->belongsToMany(Team::class);
}
}
