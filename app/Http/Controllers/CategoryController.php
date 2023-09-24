<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       return view('category');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {    $cat = Category::all();
        return view('readCategories',[
           'category'=> $cat ,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   $category        = new Category();
        $category->name  = $request->post('name');
        $category->image = $request->file('image');
     

       
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $category['image'] = $file->store('/categories' , 'public');}
        $category->save();
      
        return redirect()->route('createCategory');
    }

   public function AllCategories(){
    $all = Category::All();
    return response()->json([
   'message'=>'All categories show here',
   'data'=>$all,
   'status'=>Response::HTTP_OK, 
    ]);
   
   }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function search(Request $request)
     {
         $query = Category::query();
     
         $filter = $request->input('filter');
         $searchQuery = $request->input('query');
     
         if ($filter && $searchQuery) {
             $query->where($filter, 'like', "%$searchQuery%");
         }
     
         $healthPlaces = $query->get();
     
         return view('readCategories', ['category' => $healthPlaces]);
     }
    public function show($id)
    {
     

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {$category = Category::where('id',$id)->first()
;       return view ('updateCategory',
    ['category' =>$category]);
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
        $category = Category::findOrFail($id);
        $category->name = $request->post('name');
    
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $category->image = $file->store('/categories', 'public');
        }
    
        $category->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
