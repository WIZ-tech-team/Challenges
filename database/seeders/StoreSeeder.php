<?php

namespace Database\Seeders;

use App\Models\StoreCategory;
use App\Models\StoreProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed two categories
        $shoesCategory = StoreCategory::create(['title' => 'Shoes']);
        $shortsCategory = StoreCategory::create(['title' => 'Shorts']);

        // Seed products for Shoes category
        StoreProduct::create([
            'store_category_id' => $shoesCategory->id,
            'name' => 'Football Shoes',
            'price_in_points' => 150,
            'quantity' => 10,
            'is_available' => true,
            'image' => 'football-shoes.jpg',
        ]);

        StoreProduct::create([
            'store_category_id' => $shoesCategory->id,
            'name' => 'Running Shoes',
            'price_in_points' => 200,
            'quantity' => 8,
            'is_available' => true,
            'image' => 'running-shoes.jpg',
        ]);

        // Seed products for Shorts category
        StoreProduct::create([
            'store_category_id' => $shortsCategory->id,
            'name' => 'Football Shorts',
            'price_in_points' => 80,
            'quantity' => 12,
            'is_available' => true,
            'image' => 'football-shorts.jpg',
        ]);

        StoreProduct::create([
            'store_category_id' => $shortsCategory->id,
            'name' => 'Running Shorts',
            'price_in_points' => 70,
            'quantity' => 15,
            'is_available' => true,
            'image' => 'running-shorts.jpg',
        ]);

        // Seed product without category
        StoreProduct::create([
            'store_category_id' => null,
            'name' => 'Sports Water Bottle',
            'price_in_points' => 30,
            'quantity' => 20,
            'is_available' => true,
            'image' => 'water-bottle.jpg',
        ]);
    }
}
