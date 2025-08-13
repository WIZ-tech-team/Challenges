<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;
    
    protected $fillable = ['title'];

    protected $table = 'store_categories';

    public function products()
    {
        return $this->hasMany(StoreProduct::class, 'store_category_id');
    }
}
