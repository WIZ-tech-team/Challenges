<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\ApiUser;
use App\Models\TeamUser;
use App\Models\Challenge;
use App\Models\Invitation;
use Illuminate\Http\Request;

use Kreait\Firebase\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Contract\Auth as AuthFirebase;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $team = Team::all();
       return response()->json(
        ['message' => 'Selected users already have a team',
        'data' =>$team,
        'status'  =>Response::HTTP_OK]);

        
      
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
        $validator = Validator::make($request->all(), [
      
            'name'           => 'required',
            'user_firebase_uid'    => 'required',
            'image'          => 'required|image|mimes:png,jpg|max:2048',
           
        ]);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            $formattedErrorMessages = implode(' ', $errorMessages);
    
            return response()->json([
                'message' => $formattedErrorMessages,
                'status'  => Response::HTTP_BAD_REQUEST,
            ]);
        }
        $firebase =
        (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
        $firestore = $firebase->createFirestore();
        $database = $firestore->database();
        $usersCollection = $database->collection('Teams')->NewDocument();

        // $idToken = $request->bearerToken();
       //  $verifiedToken =app(AuthFirebase::class)->verifyIdToken($idToken);
      //  $userAuth = app(AuthFirebase::class)->getUser($verifiedToken->claims()->get('sub'));
        $userAuth    = Auth::guard('api')->user();
        $user =ApiUser::find($userAuth);
        if(! $user){
            return response()->json(
                ['message' =>  'user not found',
                
                'status'  =>Response::HTTP_NOT_FOUND]);
        }
        $usersString = $request->post('user_firebase_uid', '') ;
       
        $usersArray = explode(',', $usersString);
        $usersArray1 = explode(',', $usersString);
      // $usersArray = array_map('intval', $usersArray);
       $usersArray[] = $userAuth->firebase_uid;//add auth user to users array
       $usersToinvite = ApiUser::whereIn('firebase_uid', $usersArray1)
       //->whereNull('team_id')
       ->get();
   
        $usersWithTeam = ApiUser::whereIn('firebase_uid', $usersArray)
       
        ->get();

        if ($usersWithTeam->isNotEmpty()) {
            $usersWithTeamData = $usersWithTeam->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name'=>$user->name,
                    'firebase_uid' => $user->firebase_uid,
                    // Add other user information you want to include in the response
                ];
            });
        

           /* return response()->json([
                'message' => 'Selected users already have a team',
                'users_with_team' => $usersWithTeamData,
                'status' => Response::HTTP_BAD_REQUEST
            ]);*/

        }

    
        $team = new Team();
        $team->name = $request->post('name');
        $team->firebase_document =$usersCollection->id();
        $team->image = $request->file('image');
        
       
        if ($request->hasFile('image')) {
                     $file = $request->file('image');
                     $team['image'] = $file->store('/images' , 'public');}
                   
                     $team->save();       
       
        $userAuth->type = 'leader';
        $userAuth->team_id=$team->id;
        $userAuth->save();
$teamUser = new TeamUser();
$teamUser->team_id =$team->id; 
$teamUser->user_id =$userAuth->id; 
$teamUser->save();

        foreach ($usersToinvite as $userId) {
            $invitation = new Invitation();
            $invitation->user_id = $userId->id;
            $invitation->team_id = $team->id;
            $invitation->status = 'pending';
            $invitation->save();

            $invitationsData[]  = [
                'user_uid'      => $userId->firebase_uid,
                'invitation_id' => $invitation->id
            ];
        }
        // foreach ($usersArray as $userId) {
         
        //     $user = ApiUser::where('firebase_uid',$userId)->first();
          
        //     if ($user) {
        //         if ($user->team_id === null) {
        //             $user->team_id = $team->id;
        //             $user->save();
        //         }
        //     }
        // }
 
  

$usersCollection->set([
 'name'   => $team->name,
 'image'  => $team->image,
 'users'  => $usersArray ,
 'leader' => $userAuth->firebase_uid,


]);

        return response()->json([
            'message'     =>'Team added successfully',
            'data'        => $team,
            'document_id' => $usersCollection->id(),
            'users'       => $usersArray ,
            'leader'      => $userAuth->firebase_uid,
            'invitations' => $invitationsData,
            'status'      => Response::HTTP_OK
        ]
        );
    
      
     
    }
 public function invitation(Request $request,$id){
    $invitation = Invitation::find($id);
    $invitationUser =  $invitation->user_id;
    $user= ApiUser::find( $invitationUser);
    
    if (!$invitation) {
        return response()->json(['message' => 'Invitation not found ',
    'status'=> Response::HTTP_NOT_FOUND] );
    }

    if ( $user->team_id !== null) {
        return response()->json(['message' => 'please leave your team, then accept this invitation',
    'status'=> Response::HTTP_BAD_REQUEST] );
    }
    $action = $request->input('action');

    if ($action === 'accept') {
        $teamUser= new TeamUser();
        $invitation->status = 'accepted';
        $user = ApiUser::find($invitation->user_id);
        $user->team_id = $invitation->team_id;
        $teamUser->team_id=$user->team_id;
        $teamUser->user_id=  $user->id ;
        $teamUser->save();
        $user->type = 'member';
        $user->save();
       
       
    } elseif ($action === 'refuse') {
        $invitation->status = 'refused';
    } else {
        return response()->json(['message' => 'Invalid action','status'=> Response::HTTP_BAD_REQUEST]);
    }

    $invitation->save();

    return response()->json(['message' => 'Invitation status added ',
    'status'=> Response::HTTP_OK]);

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
        $firebase      = (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
      //  $idToken       = $request->bearerToken();
      //  $verifiedToken = app(AuthFirebase::class)->verifyIdToken($idToken);
      //  $userAuth      = app(AuthFirebase::class)->getUser($verifiedToken->claims()->get('sub'));
     //   $ModelUser     = ApiUser::where('firebase_uid', $userAuth->uid)->first();
        $ModelUser    = Auth::guard('api')->user();
      
        $team          = Team::where('firebase_document',$id)->first();
        
        if (!$team) {
            return response()->json(['message' => 'Team not found.',
                                    'status'   => Response::HTTP_NOT_FOUND]);
        }
        
        if ($ModelUser->team_id === $team->id && $ModelUser->type === 'leader') {
       

       $team->name  = $request->post('name');
       $team->image = $request->post('image');
       $users       = $request->post('user_firebase_uid',' ');
       $usersArray  = explode(',', $users);
       
       if ($request->hasFile('image')) {
        $file = $request->file('image');
        $team['image'] = $file->store('/images' , 'public');}
        $team->save();
      
       $userDataArray = [];//json
       foreach ($usersArray as $userId) {    
        $user = ApiUser::where('firebase_uid',$userId)->first();
     
        if ($user) {
            $invitation = new Invitation();
            $invitation->user_id = $user->id;
            $invitation->team_id = $team->id;
            $invitation->status = 'pending';
            $invitation->save();
            $invitationsData[]  = [
                'user_uid'      => $user->firebase_uid,
                'invitation_id' => $invitation->id
            ];
        } 
        $userData        = $user->toArray();//json
        $userDataArray[] = $userData;//json
    }
   
   
    $firestore       = $firebase->createFirestore();
    $database        = $firestore->database();
    $usersCollection = $database->collection('Teams');
    $usersCollection->document($id)->update(
    [
     ['path' => 'name',   'value' =>  $team->name],
     ['path' => 'image',  'value' => $team->image],
     ['path' => 'users',  'value' => $usersArray],
    ]);
      
    return response()->json([
        'message'       => 'Team updated successfully.',
        'data'          => $team,
        'users'         => $userDataArray,
        'leader'        => $ModelUser,
        'firebase_uids' => $usersArray, 
        'invitations'   => $invitationsData,
        'status'        => Response::HTTP_OK]);
    } else {
    return response()->json([
            'message' => 'Only team leaders can update the team.',
            'status'=>Response::HTTP_FORBIDDEN]);
    }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function myTeams(Request $request){
    $firebase =
    (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
    $firestore = $firebase->createFirestore();
    $database = $firestore->database();
 
    // $idToken = $request->bearerToken();
    // $verifiedToken =app(AuthFirebase::class)->verifyIdToken($idToken);
    // $user = app(AuthFirebase::class)->getUser($verifiedToken->claims()->get('sub'));
    // $ModelUser =ApiUser::where('firebase_uid', $user->uid)->first();
    $ModelUser    = Auth::guard('api')->user();
  
    $invitations = Invitation::where('user_id', $ModelUser->id)
    ->where('status', 'pending')
    ->with('team', 'user', 'team.apiUsers')
   ->get();
   $userNames=[];
    $formattedInvitations = [];
    foreach ($invitations as $invitation) {
        $userData = $invitation->team->apiUsers->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name, // Replace with the actual attribute name for user names
                'avatar' => $user->avatar,];
        });$userDataArray = $userData->toArray();
//$userNames = $invitation->team->apiUsers->pluck('firebase_uid')->toArray();
        $formattedInvitations[] = [
        'invitation_data'=>[
               'invitation_id'      => $invitation->id,
               'invitation_team_id' => $invitation->team_id,
               'invitation_user_id' => $invitation->user_id,
               'invitation_status'  => $invitation->status,
               'team'=>['team_id'       => $invitation->team->id,
               'team_document' => $invitation->team->firebase_document,
               'name'    => $invitation->team->name,
               'image'   => $invitation->team->image,
              ],
           
              'users'   =>$userDataArray,
                
     
            ],]; 
        // $r= $invitation->team->id;
        // $rr=Team::find($r);
        // dd($rr->apiUsers);
    }
 
    $team = Team::where('id', $ModelUser->team_id)->first();
    return response()->json([
        'message'  =>'My Team Page data',
        'User_team'=>[
            'document_id'=>$team->firebase_document,
            'id'=>$team->id,
            'name'=>$team->name,
            'image'=>$team->image,
        ],
      //  'invitations'=>$invitations,
      //  'user_invitation'=> $formattedInvitations,
        'status'=>Response::HTTP_OK,
        'invitations'=>$formattedInvitations,
     ]);
   

   }
   public function viewTeam($id){
    $team       = Team::where('id',$id)->first();
    $teamID     = $team->id;
    $challenges = Challenge::where('team_id', $teamID)->get();
    $members    = ApiUser::where('team_id', $teamID)->get();
    $membersData=[];
    foreach($members as $qqq){
     
        $membersData[]=     [  
            'id'=>$qqq->id,
             'name'  => $qqq->name,
             'avatar'=> $qqq->avatar,
             'uid'=> $qqq->firebase_uid,]
        ;
// $qqq = [
//    'name'=> $members->name,
//    'avatar'=> $members->avatar,
// ];
}
return response()->json([
    'message'  =>'My Team Page data',
    'User_team'=>[
        'document_id'=>$team->firebase_document,
        'id'        =>$team->id,
        'name'=>$team->name,
        'image'=>$team->image,
                  ],
    'Challenges'=>$challenges,
    'Members' =>$membersData,
    'status'=>Response::HTTP_OK,
 ]);

   }
     public function teamUsers($id)
    {
        $team       = Team::where('firebase_document',$id)->first();
        if(!$team){
            return response()->json([
                'message'  =>'Team Not Found',
               
                'status'=>Response::HTTP_NOT_FOUND,
             ]);
        }
        $teamID     = $team->id;
        $members    = ApiUser::where('team_id', $teamID)
        ->where('type','member')->get();
        return response()->json([
            'message'  =>'Team Users',
            'Members' =>$members,
            'status'=>Response::HTTP_OK,
         ]);
    }
}
