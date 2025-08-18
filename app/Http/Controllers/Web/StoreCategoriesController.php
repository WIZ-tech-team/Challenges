<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use Exception;
use Illuminate\Http\Request;

class StoreCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = StoreCategory::all();
        return view('dashboard.store.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.store.categories.form');
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
                'title' => 'required|string|unique:store_categories,title'
            ]);

            StoreCategory::create(['title' => $request['title']]);

            return redirect()->back()->with('success', 'Store category created successfully!');
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
        $category = StoreCategory::findOrFail($id);
        return view('dashboard.store.categories.form', compact('category'));
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

            $category = StoreCategory::findOrFail($id);

            $request->validate([
                'title' => 'required|string|unique:store_categories,title'
            ]);

            $category->title = $request['title'];
            $category->update();

            return redirect()->back()->with('success', 'Store category updated successfully!');
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
        $category = StoreCategory::findOrFail($id);
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
