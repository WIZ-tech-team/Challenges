<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\StoreOrder;
use App\Models\StoreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class StoreOrdersController extends Controller
{
    public function store(Request $request, $product_id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $product = StoreProduct::findOrFail($product_id);
        if (!$product->is_available || $product->quantity <= 0) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Product unavailable.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($user->points < $product->price_in_points) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Insufficient points.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $order = StoreOrder::create([
                'api_user_id' => $user->id,
                'store_product_id' => $product_id,
                'points' => $product->price_in_points, // Set order points equal to product points
                'status' => 'pending',
            ]);

            $user->update(['points' => $user->points - $product->price_in_points]);
            $product->update(['quantity' => $product->quantity - 1]);
            $user->save();
            $product->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully.',
                'data' => $order
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error' => $e
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
