<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreProduct extends Model
{
    use HasFactory;

    protected $fillable = ['store_category_id', 'name', 'price_in_points', 'quantity', 'is_available', 'image'];
    
    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }

    public function orders()
    {
        return $this->hasMany(StoreOrder::class);
    }
}
