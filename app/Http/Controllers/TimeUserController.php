<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\ApiUser;
use App\Models\TimeUser;
use App\Models\Challenge;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TimeUserController extends Controller
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
    public function store(Request $request, $challengeID, $team)
    {  
        $challenge = Challenge::find($challengeID);
        $team1     = Team::find($team);
        $teamid    = $team1->id;
        $AuthUser  = Auth::guard('api')->user();
      
       
        $startTime = $request->post('start_time');
        $challengeTime = $challenge->start_time;
        $challengeEndTime = $challenge->end_time;
        $user =ApiUser::find($AuthUser);
        if(! $user){
            return response()->json(
                ['message' =>  'user not found',
                
                'status'  =>Response::HTTP_NOT_FOUND]);
        }
        
        $Auth_id   = $AuthUser->id;
        $existingTimeUser = TimeUser::where('challenge_id', $challenge->id)
    ->where('team_id', $teamid)
    ->where('user_id', $Auth_id)
    ->first();
    $result = ChallengeResult::where('challenge_id', $challenge->id)
    ->where('team_id', $teamid)
    ->where('user_id', $Auth_id)
    ->first();
      if($challenge->category_id == 2 ){
      if ($startTime >= $challengeTime && $startTime < $challengeEndTime) {

    if( $AuthUser->team_id === $teamid && $challenge->team_id === $teamid){
        if ($existingTimeUser) {
            return response()->json(
                [
                    'message' => 'You have already added a start time for this challenge',
                    'status'  => Response::HTTP_CONFLICT, // Conflict status code indicates a duplicate entry
                ]
            );
        } else {
        $timeUser  = new TimeUser();
      $timeUser->challenge_id  = $challenge->id;
        $timeUser->team_id       = $teamid;
        $timeUser->user_id       = $Auth_id;
        $timeUser->UserStartTime = $startTime;
        $timeUser->save();
        $durationTime =$challengeEndTime - $startTime;
$result->challenge_duration = $durationTime;
$result->save();
        return response()->json(
          ['message' =>  'start time added successfully',
            
            'status'  =>Response::HTTP_OK]);}
}else{
    return response()->json(
        ['message' =>  'user not in this team or challenge',
        
        'status'  =>Response::HTTP_OK]);
}  } 
else {
   
    return response()->json(
        ['message' =>  'Time should between start and end time for the challlenge',

        'status'  =>Response::HTTP_BAD_REQUEST]);
       
}
       
    }else{
    
            return response()->json(
                ['message' =>  'challenge category not running',
                
                'status'  =>Response::HTTP_BAD_REQUEST]);
           
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
        //
    }
}
