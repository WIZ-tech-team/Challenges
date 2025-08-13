<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use App\Models\StoreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StoreProductsController extends Controller
{
    public function productsByCategory($category_id)
    {

        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($category_id === 'all') {
            $products = StoreProduct::all();
        } else {
            $category = StoreCategory::with('products')->findOrFail($category_id);
            $products = $category->products;
        }

        return response()->json([
            'status' => 'success',
            'data' => $products
        ], Response::HTTP_OK);
    }
}
