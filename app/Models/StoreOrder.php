<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    use HasFactory;

    protected $fillable = ['api_user_id', 'store_product_id', 'points', 'status'];
    
    public function apiUsers()
    {
        return $this->belongsTo(ApiUser::class, 'api_user_id');
    }

    public function product()
    {
        return $this->belongsTo(StoreProduct::class, 'store_product_id');
    }
}
