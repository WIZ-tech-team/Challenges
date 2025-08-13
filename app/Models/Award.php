<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'challenge_id',
        'name',
        'for_rank',
        'details'
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function products()
    {
        return $this->belongsToMany(StoreProduct::class, 'award_store_product');
    }

}
