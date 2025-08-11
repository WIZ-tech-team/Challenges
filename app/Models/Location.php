<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Location extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'latitude',
        'longitude',
        'title',
        'address',
        'description'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('location_image')
            ->singleFile()
            ->registerMediaConversions(function ($media) {
                $this->addMediaConversion('thumb')->width(300)->height(300);
            });
    }

}
