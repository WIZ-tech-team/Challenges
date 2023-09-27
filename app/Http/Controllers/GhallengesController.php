<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\ApiUser;
use App\Models\Category;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Contract\Auth as AuthFirebase;

class GhallengesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $challenge = Challenge::all();
      return $challenge;
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
    public function store(Request $request,$teamID)
    {   $validator    = Validator::make($request->all(),[
        'title'       => ['required', 'string', 'max:255'],
        'category_id' => ['required', 'string', 'max:255'],
        'start_time'  => ['required', 'date_format:Y-m-d H:i:s', 'max:255'],
        'end_time'    => ['required', 'date_format:Y-m-d H:i:s', 'max:255'],
       
        'latitude'    => ['required', 'numeric', 'max:255'],
        'longitude'   => ['required', 'numeric', 'max:255'],
    ], );
    
    if ($validator->fails()) {
        $errorMessages = $validator->errors()->all();
        $formattedErrorMessages = implode(' ', $errorMessages);

        return response()->json([
            'message' => $formattedErrorMessages,
            'status'  => Response::HTTP_BAD_REQUEST,
        ], Response::HTTP_BAD_REQUEST);
    }
      
        
        $team = Team::where('firebase_document',$teamID)->first();
        $teamID1= $team->id;
       
        $usersTeam = ApiUser::where('team_id', $teamID1)->get();
        
      foreach ($usersTeam as $user) {
         $user->points  += 2;
         $user->save();
                                   }
        $leader = Auth::guard('api')->user();
        if(!$leader){
            return response()->json([
                'message'=>'user not found'
            ]);
        }
        $id = $leader->id;
        $leaderTeam = $leader->team_id;
        if( $leaderTeam == $teamID1 && $leader->type =='leader'){

       
        
        $category= $request->post('category_id');
    
        $categoryExists = Category::where('id',  $category)->first();
        if (!$categoryExists) {
            return response()->json(['message' => 'Invalid category_id provided',
            'status'=> Response::HTTP_BAD_REQUEST]);
        }
        $title      = $request->post('title');
        $latitude   = $request->post('latitude') ;
        $longitude  = $request->post('longitude') ;
        $start_time = $request->post('start_time') ;
        $end_time   = $request->post('end_time') ;
       
        $opponent_id= $request->post('opponent_id');
        $opponent_firebase= Team::where('firebase_document',$opponent_id)->first();
       
       
        $challenge = new Challenge();
        $challenge->title =$title ;
        $challenge->type ='private';
        $challenge->latitude = $latitude;
        $challenge->longitude = $longitude;
        $challenge->category_id =$category;
        $challenge->team_id =$teamID1;
        $challenge->start_time = $start_time ;
        $challenge->end_time =  $end_time;
       
       

        if ($category == 1) {
            if (!$opponent_firebase) {
                return response()->json([
                    'message' => 'Opponent not found',
                    'status' => Response::HTTP_NOT_FOUND,
                ]);}
            if ($opponent_id == $teamID) {
                return response()->json([
                    'message' => 'Opponent cannot be the same as your team',
                    'status' => Response::HTTP_BAD_REQUEST,
                ]);
            }
        $refree = $usersTeam->pluck('firebase_uid')->toArray();
    
        $RefreeId = $request->post('refree_id');
        $refree_firebase= ApiUser::where('firebase_uid',$RefreeId)->first();

        // Check if the provided refree_id is in the list of valid referee IDs
        if (!$refree_firebase) {
            return response()->json(['message' => 'Invalid refree user',
            'status'=>Response::HTTP_NOT_FOUND] );
        }
            $challenge->refree_id = $refree_firebase->id;
            $challenge->opponent_id= $opponent_firebase->id;

        } else {
            $challenge->refree_id = null;
            $challenge->opponent_id= null;
        }
    
        // Set the stepsNum based on the category ID
        if ($category == 2) {
            $challenge->stepsNum = $request->post('stepsNum');
            $challenge->distance =$request->post('distance') ;
            
        } else {
            $challenge->stepsNum = null;
            $challenge->distance =null;
        }
     

        $firebase =
        (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
        $firestore = $firebase->createFirestore();
        $database = $firestore->database();
        $challengeRef = $database->collection('Challenges')->NewDocument();
        $challengeData=[
            'title'       => $title,
            'type'        => 'private',
            'latitude'    => $latitude,
            'longitude'   => $longitude,
            'category'    => $category,
            'refree_id'   => '',
            'opponent_id' => '',
            'teamID'      => $team->firebase_document,
            'start_time'  => $start_time,
            'end_time'    => $end_time,
           

        ];
        $challenge->document_id = $challengeRef->id();
        $challenge->save();
        if ($category == 1) {
            $challengeData['refree_id'] = $refree_firebase->firebase_uid;
            $challengeData['opponent_id']= $opponent_firebase->firebase_document;
            $challengeData['stepsNum'] = null;
            $challengeData['distance'] =null;
        } elseif($category == 2){
            $challengeData['stepsNum'] = $request->post('stepsNum');
            $challengeData['distance'] =$request->post('distance') ;
            $challengeData['refree_id'] = null;
            $challengeData['opponent_id']= null;
        }
    
        // Set the stepsNum based on the category ID
        
       
        $challengeRef->set($challengeData);
        return response()->json([
            'message' => 'Challenge added successfully',
            'data' => $challengeData,
            'document_id'=>$challengeRef->id(),
            'challenge_name'=>$categoryExists->name,
            'status' => Response::HTTP_OK,
        ]);}else {
            return response()->json([
                'message' => 'Only team leader can add challenges to this team',
           ]);
        }
    }
   
//     public function start(Request $request,$challengeID){
//         $user =Auth::guard('api')->user();
//         $userId = $user->id;
//         $challenge = Challenge::find($challengeID);
//         if($challenge->category_id == 2 && $challenge->team_id == $user->team_id )
//         {$action = $request->action;
//         $challenge->status = 'started';
//          $challenge->save();
// }else{
//     return response()->json([
//         'challenge category must be running'
//     ]);
// }

//     }
    
    public function show(Request $request)
    {   
        $firebase =
        (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
        $firestore = $firebase->createFirestore();
        $database  = $firestore->database();
     
        // $idToken       = $request->bearerToken();
        // $verifiedToken = app(AuthFirebase::class)->verifyIdToken($idToken);
        // $user1         = app(AuthFirebase::class)->getUser($verifiedToken->claims()->get('sub'));
        // $user          = ApiUser::where('firebase_uid', $user1->uid)->first();
         $user    = Auth::guard('api')->user();
         if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  =>Response::HTTP_NOT_FOUND,
            ]);
        }
      $q=$user->load('team');
       
        $userId = $user->id;
        $teamId = $user->team_id;
        $challenges = Challenge::with(['category', 'team'])
    ->join('team_users', 'challenges.team_id', '=', 'team_users.team_id')
    ->where('team_users.user_id', $userId)
    ->where('team_users.team_id', $teamId) // Check if user is still a member of the team
    ->select('challenges.*')
    ->get();
      
    //     $challenges = Challenge::with('category')
    //    ->where('refree_id', $userId)
    //    ->orWhere('team_id', $teamId)
    //     ->get();

    $challengeIds = DB::table('challenges_api_users')
        ->where('users_id', $user->id)
        ->pluck('challenge_id');
        
    
    $challengesByApiUsersTable = Challenge::with('category')
        ->whereIn('id', $challengeIds)
        ->get();
    
    // // Merge the two sets of challenges
    $mergedChallenges = $challenges->merge($challengesByApiUsersTable);
    
    return response()->json([
        'message' => 'User profile here',
        'user_data' => $q,
        'challenges' => $mergedChallenges,
        'status' => Response::HTTP_OK,
    ], 200);
        
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
 
       public function challenges(){
        /*  $user    = Auth::guard('api')->user();
         if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  =>Response::HTTP_NOT_FOUND,
            ]);
        }
        $userId = $user->id;
        $teamId = $user->team_id;

        $challenges = Challenge::with('category')
        ->where('refree_id', $userId)
       ->orWhere('team_id', $teamId)
        ->get();
    
    $challengeIds = DB::table('challenges_api_users')
        ->where('users_id', $user->id)
        ->pluck('challenge_id');
    
    $challengesByApiUsersTable = Challenge::with('category')
        ->whereIn('id', $challengeIds)
        ->get();
    
    // Merge the two sets of challenges
    $mergedChallenges = $challenges->merge($challengesByApiUsersTable);
    
    return response()->json([
        'message' => 'User profile here',
        'user_data' => [
            'name' => $user->name,
            'avatar' => $user->avatar,
            'points' => $user->points,
            'uid' => $user->firebase_uid,
        ],
        'challenges' => $mergedChallenges,
        'status' => Response::HTTP_OK,
    ], 200);
        */
        $user    = Auth::guard('api')->user();

        if (!$user) {
           return response()->json([
               'message' => 'User not found.',
               'status'  =>Response::HTTP_NOT_FOUND,
           ]);
       }
       
       $userId = $user->id;
       $teamId = $user->team_id;
//        $challenges = Challenge::with(['category', 'team','opponent'])
//    ->join('api_users', 'challenges.team_id', '=', 'api_users.team_id')
//    ->where('api_users.id', $userId)
   
//    ->select('challenges.*')
//    ->get();
   $challenges = Challenge::with(['category', 'team', 'opponent', 'results'])
   ->join('api_users', 'challenges.team_id', '=', 'api_users.team_id')
   ->leftJoin('challenge_results', 'challenges.id', '=', 'challenge_results.challenge_id')
   ->where('api_users.id', $userId)
   ->select('challenges.*', 'challenge_results.result_data', 'challenge_results.opponent_result')
   ->get();
    
    
      return response()->json( ['message' =>'User challenges here ',
        'User challenges'=> $challenges,
     
        'status'=>Response::HTTP_OK]);
    }
 
    public function viewChallenge($id){
        
        $user    = Auth::guard('api')->user();
        if (!$user) {
           return response()->json([
               'message' => 'User not found.',
               'status'  =>Response::HTTP_NOT_FOUND,
           ]);
       }
       
      $userTeam = $user->team_id;
          $challenge = Challenge::with(['category', 'team', 'opponent'])
          ->where('id',$id)
         ->where('team_id',$userTeam)
          ->first();

         if(!$challenge){
            return response()->json([
             'message'=>'Challenge not found',
             'status'=>Response::HTTP_NOT_FOUND,
            ]);
          }
          $userResults = $challenge->results->where('user_id', $user->id);
          $latestUserResult = $userResults->last();
          $challenge->setAttribute('user_result', $latestUserResult);
      
      return response()->json([
            'message'=>'Challenge Data',
           'data'=>$challenge,
           'status'=>Response::HTTP_OK
         
              ]);
    }

}
