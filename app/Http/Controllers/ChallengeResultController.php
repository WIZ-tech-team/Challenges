<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\ApiUser;
use App\Models\TimeUser;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\ChallengeResult;
use Illuminate\Support\Facades\Auth;

class ChallengeResultController extends Controller
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
    $userIds1 = $request->input('user_id');
    $userIds = ApiUser::find($userIds1);
    $challenge = Challenge::find($challengeID);
    $team1 = Team::find($team);
    $teamid = $team1->id;
    $AuthUser = Auth::guard('api')->user();
   
    $result = $request->post('football_result_data');
    $opponentResult = $request->post('opponent_result');

    // Check if a record with the same challenge_id, team_id, and user_id (if applicable) already exists
    $existingResult1 = ChallengeResult::where('challenge_id', $challenge->id)
    //->where($challenge->status,'ended')
        ->where('team_id', $teamid)
        ->whereNotNull('result_data')
        ->whereNotNull('opponent_result')
        ->first();

        $existingResult = ChallengeResult::where('challenge_id', $challenge->id)
       // ->where($challenge->status,'started')
        ->where('team_id', $teamid)
        ->when($userIds1, function ($query) use ($userIds1) {
            return $query->where('user_id', $userIds1);
        })
        ->first();
        
        if ($challenge->category_id == 1 && $challenge->status != 'ended') {
            if(!$AuthUser){
                return response()->json([
                    'message'=>'user not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ]);
            }
            $Auth_id =$AuthUser->id;
            // Check if the challenge is not ended
            return response()->json(['message' => 'Football Challenge is not ended yet', 'status' => Response::HTTP_BAD_REQUEST]);
          } elseif ($challenge->category_id == 1 ) {
            if ($challenge->team_id === $teamid && $AuthUser->team_id === $teamid &&$AuthUser->type === 'leader') {
                if ($existingResult1) {
                // Update the existing record
                $existingResult1->result_data = $result;
                $existingResult1->opponent_result = $opponentResult;
                $existingResult1->save();
                return response()->json(['message' => 'Football Challenge result updated successfully', 'status' => Response::HTTP_OK]);
            } else {
                // Create a new record
                $challengeResult = new ChallengeResult();
                $challengeResult->challenge_id = $challenge->id;
                $challengeResult->team_id = $teamid;
                $challengeResult->result_data = $result;
                $challengeResult->opponent_result = $opponentResult;
                
                $challenge->save();
                $challengeResult->save();
                return response()->json(['message' => 'Football Challenge result added successfully', 'status' => Response::HTTP_OK]);
            }
        } else {
            // Either the provided team is not related to the challenge or the category is not football
            return response()->json(['message' => 'Invalid team or leader for the challenge', 'status' => Response::HTTP_BAD_REQUEST]);
        }
    } elseif ($challenge->category_id == 2 && $challenge->status != 'started') {
        return response()->json(['message' => 'You cannot enter any result of this running challenge ', 'status' => Response::HTTP_BAD_REQUEST]);
      } elseif ($challenge->category_id == 2  && $challenge->status ==='started'  && $userIds->team_id === $teamid ) {
        $userIds = $request->input('user_id');
        $resultDataArray = $request->post('result_data');
        $user = ApiUser::find($userIds);
     
        if ($existingResult) {
            // Update the existing record
            $existingResult->result_data = $resultDataArray;
            $existingResult->save();
            return response()->json(['message' => 'Running result updated successfully', 'status' => Response::HTTP_OK]);

        } else {
            // Create a new record
            $result = new ChallengeResult([
                'result_data' => $resultDataArray,
            ]);
            $result->user_id = $user->id;
            $result->challenge_id = $challenge->id;
            $result->team_id = $teamid;
            $result->save();
            $existingTimeUser = TimeUser::where('challenge_id', $challenge->id)
            ->where('team_id', $userTeam)
            ->where('user_id', $id)
            ->first();
             if($existingTimeUser){
                $result->challenge_duration = $existingTimeUser->challenge_duration;
             }
             $result->save();
        }

        return response()->json(['message' => 'Running results submitted successfully', 'status' => Response::HTTP_OK]);
    }

    return response()->json(['message' => 'This user is not a team member', 'status' => Response::HTTP_BAD_REQUEST]);
        }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($challenge)
    {   $challenge = Challenge::find($challenge);
        $challengeId = $challenge->id;
        $user   = Auth::guard('api')->user();
        if(!$user){
            return response()->json([
                'message'=>'user not found'
            ]);
        }
        $id = $user->id;
        $leader = ApiUser::where('id',$id)->first();
        $userTeam = $leader->team_id;
        $categoryId = $challenge->category_id; 
        $results = ChallengeResult::where('challenge_id',$challengeId)->get();
      
        $runningResults = [];
        $footballResults = [];
        
        foreach ($results as $result) {
            $runningResult = [
                   'result_id'   => $result->id,
                   'user_id'     => optional($result->user)->id,
                   'user_name'   => optional($result->user)->name,
                   'user_image'  => optional($result->user)->avatar,
                   'user_result' => $result->result_data,
                   'challenge_id'=> $result->challenge_id,
            ];
        
            $footballResult = [
                'id'              => $result->id,
                'team_result'     => $result->result_data,
                'opponent_result' => $result->opponent_result,
                'challenge_id'    => $result->challenge_id,
                'team_data'=>[
                    'id'=>optional($result->team)->id,
                    'firebase_document'=>optional($result->team)->firebase_document,

                    'name'=>optional($result->team)->name,
                    'image'=>optional($result->team)->image,
                ],
                'opponent_team_data'=>[
                    'id'=>optional($result->opponent)->id,
                    'firebase_document'=>optional($result->opponent)->firebase_document,

                    'name'=>optional($result->opponent)->name,
                    'image'=>optional($result->opponent)->image,
                ]
                
            ];
        
            $runningResults[]  = $runningResult;
            $footballResults[] = $footballResult;
        }
        
        if( $categoryId == 2){
            return response()->json(['message' => 'Challenge results here',
             'challenge_data'=> $challenge,
             'results'       => $runningResults,
             'status'        => Response::HTTP_OK,]);

        }elseif($categoryId == 1){
          return response()->json(['message' => 'Challenge results here',
            'challenge_data'=> $challenge,
            'results'       => $footballResults ,
            'status'        => Response::HTTP_OK,]);}}
       


         public function showRunning($challenge)
            {  $challenge =Challenge::where('id', $challenge)
                //->where('category_id', '2')
                ->first();
                if(!$challenge){
                    return response()->json([
                        'message'=> 'challenge not found',
                        'status'=> Response::HTTP_NOT_FOUND,

                    ]);
                }
                $categoryId = $challenge->category_id; 

                if($categoryId  !== 2){
                    return response()->json([
                        'message'=> 'We can only offer the running challenge ',
                        'status'=> Response::HTTP_BAD_REQUEST,
                    ]);
                }else{
                 // $challenge = Challenge::find($challenge)->where('category_id','1');
                $challengeId = $challenge->id;
                $user   = Auth::guard('api')->user();
                if(!$user){
                    return response()->json([
                        'message'=> 'user not found',
                        'status'=> Response::HTTP_NOT_FOUND,

                    ]);
                }
                $id = $user->id;
                $leader = ApiUser::where('id',$id)->first();
                $userTeam = $leader->team_id;
                $categoryId = $challenge->category_id; 
                $results = ChallengeResult::where('challenge_id',$challengeId)->get();
                $runningResults = [];
                $footballResults = [];
                
                foreach ($results as $result) {
                    $runningResult = [
                           'result_id'   => $result->id,
                           'user_id'     => optional($result->user)->id,
                           'user_name'   => optional($result->user)->name,
                           'user_image'  => optional($result->user)->avatar,
                           'user_result' => $result->result_data,
                           'challenge_id'=> $result->challenge_id,
                    ];
                
               
                    $runningResults[]  = $runningResult;
                  
                }
                usort($runningResults, function ($a, $b) {
                    return $b['user_result'] - $a['user_result'];
                });
                $rank = 1;
foreach ($runningResults as &$result) {
    $result['ranking_id'] = $rank;
    $rank++;
}
               
                    return response()->json(['message' => 'Challenge results here',
                     'challenge_data'=> $challenge,
                     'results'       => $runningResults,
                     'status'        => Response::HTTP_OK,]);
        
               }}  
    
               public function showFootball($challenge)
               {  $challenge =Challenge::where('id', $challenge)
                   //->where('category_id', '2')
                   ->first();
                   if(!$challenge){
                       return response()->json([
                           'message'=> 'challenge not found',
                           'status'=> Response::HTTP_NOT_FOUND,
   
                       ]);
                   }
                   $categoryId = $challenge->category_id; 
   
                   if($categoryId  !== 1){
                       return response()->json([
                           'message'=> 'We can only offer the football challenge ',
                           'status'=> Response::HTTP_BAD_REQUEST,
                       ]);
                   }else{
                    // $challenge = Challenge::find($challenge)->where('category_id','1');
                   $challengeId = $challenge->id;
                   $user   = Auth::guard('api')->user();
                   if(!$user){
                       return response()->json([
                           'message'=> 'user not found',
                           'status'=> Response::HTTP_NOT_FOUND,
   
                       ]);
                   }
                   $id = $user->id;
                   $leader = ApiUser::where('id',$id)->first();
                   $userTeam = $leader->team_id;
                   $categoryId = $challenge->category_id; 
                   $results = ChallengeResult::where('challenge_id',$challengeId)->get();
                   $runningResults = [];
                   $footballResults = [];
                   
                   foreach ($results as $result) {
                       $runningResult = [
                              'result_id'   => $result->id,
                              'user_id'     => optional($result->user)->id,
                              'user_name'   => optional($result->user)->name,
                              'user_image'  => optional($result->user)->avatar,
                              'user_result' => $result->result_data,
                              'challenge_id'=> $result->challenge_id,
                       ];
                   
                  
                       $footballResult = [
                        'id'              => $result->id,
                        'team_result'     => $result->result_data,
                        'opponent_result' => $result->opponent_result,
                        'challenge_id'    => $result->challenge_id,
                        'team_data'=>[
                            'id'=>optional($result->team)->id,
                            'firebase_document'=>optional($result->team)->firebase_document,
        
                            'name'=>optional($result->team)->name,
                            'image'=>optional($result->team)->image,
                        ],
                        'opponent_team_data'=>[
                            'id'=>optional($result->opponent)->id,
                            'firebase_document'=>optional($result->opponent)->firebase_document,
        
                            'name'=>optional($result->opponent)->name,
                            'image'=>optional($result->opponent)->image,
                        ]
                        
                    ];
                
                  
                    $footballResults[] = $footballResult;
   }
                  
                       return response()->json(['message' => 'Challenge results here',
                        'challenge_data'=> $challenge,
                        'results'       => $footballResults,
                        'status'        => Response::HTTP_OK,]);
           
                  }} 
    public function edit($id)
    {
       
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
