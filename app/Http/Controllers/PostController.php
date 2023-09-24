<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       return view('posts');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       $post =Post::all();
       return view('readPost', [
        'post'=>$post,
       ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $request->validate(  [      
            'title'           => 'required',
            'descreption'    => 'required',
            'image'          => 'required|image|mimes:png,jpg|max:2048',
           
        ]);
        $post = new Post();
        $post->title = $request->post('title');
        $post->descreption = $request->post('descreption');
        $post->image = $request->file('image');

       
                 if ($request->hasFile('image')) {
                     $file = $request->file('image');
                     $post['image'] = $file->store('/images' , 'public');}
                   
                  
        $post->save();
        return redirect('#')->with('success','Post has been successfully added !');

    }


    public function AllPosts(){
        $post = Post::all(); 
        return response()->json([
            'message' =>'All posts show here',
            'data'    =>$post,
            'status'  =>Response::HTTP_OK, 
             ]);

    }

    public function search(Request $request)
    {
        $query = Post::query();
    
        $filter = $request->input('filter');
        $searchQuery = $request->input('query');
    
        if ($filter && $searchQuery) {
            $query->where($filter, 'like', "%$searchQuery%");
        }
    
        $blogPosts = $query->get();
    
        return view('readPost', ['post' => $blogPosts]);
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
        $health = Post::destroy($id);
       
        return redirect('/readPost');
    }
}
