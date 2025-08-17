<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Award;
use App\Models\Challenge;
use App\Models\StoreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AwardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $awards = Award::all();
        return view('dashboard.awards.index', compact('awards'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $challenges = Challenge::all();
        $storeProducts = StoreProduct::all();
        return view('dashboard.awards.create', compact('challenges', 'storeProducts'));
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
            DB::beginTransaction();

            $validated = $request->validate([
                'challenge_id' => 'required|exists:challenges,id',
                'name' => 'required|string|max:255',
                'rank' => 'required|integer|min:1',
                'details' => 'nullable|string|max:1000',
                'products' => 'nullable|array|min:1',
                'products.*' => 'integer|exists:store_products,id',
            ]);

            $award = new Award();
            $award->challenge_id = $validated['challenge_id'];
            $award->name = $validated['name'];
            $award->for_rank = $validated['rank'];
            $award->details = $validated['details'] ?? '';
            $award->save();

            if (isset($validated['products'])) {
                foreach ($validated['products'] as $productId) {
                    $storeProduct = StoreProduct::findOrFail($productId);
                    $award->products()->attach($storeProduct->id);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Award created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $award = Award::findOrFail($id);
        $award->delete();
        return redirect()->back()->with('success', 'Award deleted successfully!');
    }
}
