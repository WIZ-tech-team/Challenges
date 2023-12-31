<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Models\footballcylic;
use Illuminate\Http\Response;

class footballcylicontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cylic = new footballcylic();

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
       $cylic = footballcylic::where('id',$id)->first();
    
       $teamIds = $request->post('team_id');
       $findTeam = Team::find($team);
       if(! $findTeam){
          return response()->json([
         'message'=>'Team not found',
         'status'=> Response::HTTP_NOT_FOUND,
         ]);
       }
       
       $cylic->teams()->attach($team);
       
       

       return response()->json([
        'message'=>'Football cylic',
        'data'=>$cylic,
        'status'=> Response::HTTP_OK,
      ]);
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
