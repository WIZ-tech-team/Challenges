<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use App\Models\StoreProduct;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoreProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = StoreProduct::with('category')->get();
        return view('dashboard.store.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = StoreCategory::all();
        return view('dashboard.store.products.form', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required|string|unique:store_products,name',
                'store_category_id' => 'required|integer|exists:store_categories,id',
                'price_in_points' => 'required|numeric|min:0',
                'quantity' => 'required|integer',
                'is_available' => 'required|boolean',
                'image' => 'required|image'
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('storeProducts', 'public');
            }

            $product = StoreProduct::create([
                'name' => $request['name'],
                'store_category_id' => $request['store_category_id'],
                'price_in_points' => $request['price_in_points'],
                'quantity' => $request['quantity'],
                'is_available' => $request['is_available'],
                'image' => $imagePath
            ]);

            return redirect()->back()->with('success', 'Store product created successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = StoreProduct::with('category')->findOrFail($id);
        $categories = StoreCategory::all();
        return view('dashboard.store.products.form', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $product = StoreProduct::findOrFail($id);

            $request->validate([
                'name' => ['required', 'string', Rule::unique('store_products', 'name')->ignore($product->id)],
                'store_category_id' => 'required|integer|exists:store_categories,id',
                'price_in_points' => 'required|numeric|min:0',
                'quantity' => 'required|integer',
                'is_available' => 'required|boolean',
                'image' => 'nullable|image'
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('storeProducts', 'public');
            }

            $product->update([
                'name' => $request['name'],
                'store_category_id' => $request['store_category_id'],
                'price_in_points' => $request['price_in_points'],
                'quantity' => $request['quantity'],
                'is_available' => $request['is_available'],
                'image' => $imagePath
            ]);

            return redirect()->back()->with('success', 'Store product updated successfully!');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = StoreProduct::findOrFail($id);
        $product->delete();

        return redirect()->back()->with('success', 'Store product deleted successfully.');
    }
}
