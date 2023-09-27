<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\ApiUser;
use App\Models\Category;
use App\Models\Challenge;
use Illuminate\Http\Request;
use App\Models\footballcylic;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PublicChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { $category1= Category::all();
      $refree=ApiUser::all();
       return view ('publicChallenge',[
        'category1' =>$category1,
        'refree'  =>$refree,
       ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $Challenge = Challenge::where('type','public')
        ->where('category_id',1)
        ->get();
        return view ('readChallenges',[
            'Challenge'=> $Challenge,
        ]
    );   
    }
    public function readRunning()
    {  
        $Challenge = Challenge::where('type','public')
        ->where('category_id',2)
        ->get();
      

        $users = DB::table('challenges_api_users')
        ->where('challenge_id',16)->get();
       
        return view ('readRunningPublic',[
            'Challenge'=> $Challenge,
            'users'=> $users,
        ]
    );   
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
      
            'title'       => 'required',
            'latitude'    => ['required', 'numeric', 'max:255'],
            'longitude'   => ['required', 'max:255'],
            'start_time'  => ['required', 'date_format:Y-m-d H:i:s', 'max:255'],
            'end_time'    => ['required', 'date_format:Y-m-d H:i:s', 'max:255'],
            'attribute' => 'required',
            'category_id' => 'required',
            'image'       => 'required|image|mimes:png,jpg|max:2048',
            'winner_points' => 'required',
           
        ]);
        $title       = $request->post('title');
        $latitude    = $request->post('latitude') ;
        $longitude   = $request->post('longitude') ;
        $start_time  = $request->post('start_time') ;
        $end_time    = $request->post('end_time') ;
        $opponent_id = $request->post('opponent_id');
        $refree_id   = $request->post('refree_id');
        $category_id = $request->input('category_id');
        $distance    = $request->post('distance');
        $stepsNum    = $request->post('stepsNum');
        $team_id     = $request->post('team_id');
        $prize       = $request->post('prize');
        $image       = $request->post('image');
        $points      = $request->post('winner_points');
        $users       = $request->post('users_id',[]);
        $attribute    = $request->post('attribute');
       
        if($users == []){
            $usersRel=[];
        }else{
            $usersRel = explode(',', $users);
        }

        $challenge              =  new Challenge();
        $challenge->title       = $title;
        $challenge->type        = 'public';
        $challenge->latitude    = $latitude;
        $challenge->longitude   = $longitude;
        $challenge->start_time  = $start_time;
        $challenge->end_time    = $end_time;
       
        $challenge->opponent_id = $opponent_id;
        $challenge->refree_id   = $refree_id;
        $challenge->category_id = $category_id;
        $challenge->distance    = $distance;
        $challenge->stepsNum    = $stepsNum;
        $challenge->prize       = $prize;
        $challenge->image       = $image; 
        $challenge->attribute       = $attribute;
        $challenge->winner_points   = $points;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $challenge['image'] = $file->store('/images' , 'public');}
        if ($category_id == 1) {
            $challenge->refree_id = $refree_id;
            $challenge->opponent_id=$opponent_id;
        } else {
            $challenge->refree_id = null;
            $challenge->opponent_id= null;
        }
    
        // Set the stepsNum based on the category ID
        if ($category_id == 2) {
            $challenge->stepsNum = $stepsNum;
            $challenge->distance = $distance ;
        } else {
            $challenge->stepsNum = null;
            $challenge->distance =null;
        }
       
        $challenge->save();
        $challenge->users()->attach($usersRel);
        if($challenge->attribute == 'cylic'){
           $football = new footballcylic();
           $football->challenge_id = $challenge->id;
        }
        $football->save();
        return redirect('#')->with('success','Public challenge has been successfully added !');

      
    
    }

    public function search(Request $request)
    {
        
        $query = Challenge::query();
    
        $filter = $request->input('filter');
        $searchQuery = $request->input('query');
    
        if ($filter && $searchQuery) {
            $query->where($filter, 'like', "%$searchQuery%");
        }
        $query->where('category_id', 1);
        $challenge = $query->get();
    
        return view('readChallenges', ['Challenge' => $challenge]);
    }

    public function searchRunning(Request $request)
    {
        $query = Challenge::query();
    
        $filter = $request->input('filter');
        $searchQuery = $request->input('query');
    
        if ($filter && $searchQuery) {
            $query->where($filter, 'like', "%$searchQuery%");
        }
      $query->where('category_id', 2);
        $challenge = $query->get();
    
        return view('readRunningPublic', ['Challenge' => $challenge]);
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
       $challenge   = Challenge::find($id);
       if(!$challenge){return response()->json([
        'message' => 'Challenge Not Found',
       
        'status' => Response::HTTP_NOT_FOUND,
    ]);}
       if ($challenge->type === 'public') {
       $category  = $challenge->category_id;
       $team      = $request->post('team_id');
       $teamF     = Team::where('firebase_document',$team)->first();
       $opponent  = $request->post('opponent_id');
       $opponentF = Team::where('firebase_document',$opponent)->first();
       $firebaseUids22 = $request->post('users_id', []);
       $firebaseUids = explode(',', $firebaseUids22);
       $usersRel = [];
      
       if ($category == 1) {
        if (!$teamF) {
            return response()->json(['message' => 'Team not found.','status'=> Response::HTTP_NOT_FOUND]);
        }
        if(!$opponentF){
            return response()->json(['message' => 'Opponent Team not found.','status'=> Response::HTTP_NOT_FOUND]);

        }
        $challenge->team_id = $teamF->id;
        $challenge->opponent_id = $opponentF->id;
        $challenge->save();
        
        $challenge->users()->detach();
        if ($teamF) {
            $usersInTeam = ApiUser::where('team_id', $teamF->id)->get();
            
            foreach ($usersInTeam as $user) {
                $user->points += 2;
                $user->save();
            }
        }
        if ($opponentF) {
            $usersInTeam = ApiUser::where('team_id', $teamF->id)->get();
            
            foreach ($usersInTeam as $user) {
                $user->points += 2;
                $user->save();
            }
        }
       
    }   elseif ($category == 2) {
        foreach ($firebaseUids as $firebaseUid) {
            $user = ApiUser::where('firebase_uid', $firebaseUid)->first();
            if ($user) {
              $usersRel[] = $user->id;
              $user->points +=2;
              $user->save();
             
      } elseif(!$user){
          $notFoundUsers[] = $firebaseUid;
                  return response()->json(['message' => 'User not found.','data'=>  $notFoundUsers,'status'=>Response::HTTP_NOT_FOUND]);
  
              }
  }
        $challenge->team_id = null;
        $challenge->opponent_id = null;
        $challenge->save();
        $challenge->users()->sync($usersRel);
       
    }
    return response()->json([
        'message' => 'Challenge updated successfully',
        'data' => $challenge,
        'status' => Response::HTTP_OK,
    ]);
} else {
    return response()->json(['message' => 'Only public challenges can be updated.','status'=>Response::HTTP_FORBIDDEN]);
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
        $public = Challenge::destroy($id);
       
        return back();
    }
}
