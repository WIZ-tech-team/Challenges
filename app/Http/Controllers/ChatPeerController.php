<?php

namespace App\Http\Controllers;


use App\Models\ApiUser;
use App\Models\ChatPeer;
use Kreait\Firebase\Auth;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

use Illuminate\Http\Response;

use Kreait\Firebase\Database;
use Kreait\Firebase\ServiceAccount;
use Illuminate\Support\Facades\Auth as A;
use Kreait\Firebase\Contract\Auth as AuthFirebase;

class ChatPeerController extends Controller
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
   $firebase =
   (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
   $firestore = $firebase->createFirestore();
   $database = $firestore->database();

   $idToken = $request->bearerToken();
   $verifiedToken =app(AuthFirebase::class)->verifyIdToken($idToken);
   $user = app(AuthFirebase::class)->getUser($verifiedToken->claims()->get('sub'));
   $ModelUser =ApiUser::where('firebase_uid', $user->uid)->first();//SQL header user 
   $userId= $request->post('user_id');//request user by firebase uid
   $recUID= app(AuthFirebase::class)->getUser($userId);//firebase request user
   $recId =ApiUser::where('firebase_uid', $recUID->uid)->first();//SQL request user
   $relationUsers=[
        $ModelUser->id,
        $recId->id
                 ];
   $chatRef = $database->collection('Chat');
   $chatQuery = $database->collection('Chat')
   ->where('type', '=', 'peer')
   ->where('created_by','==', $user->uid)
   ->where('participants1.'.$user->uid, '==', true)
   ->where('participants1.'.  $recUID->uid, '==', true);
   ;
 $chatSnapshot = $chatQuery->documents();
 $chatDocuments = $chatSnapshot->rows();

 if (count($chatDocuments) === 0) {
   $chatId = $chatRef->newDocument();


   $chat = New ChatPeer();
   $chat->created_by     = $ModelUser->id; 
   $chat->chat_id    = $chatId->id();
   $chat->type = 'peer';
   $chat->save();
   $chat->participants()->attach($relationUsers);

   $chatData = [
    'created_by' => $user->uid,
    'type' => 'peer',
    'participants' => [
        $user->uid,
        $recUID->uid,
      ],
      'participants1' => [
        $user->uid=>true,
        $recUID->uid=>true,
      ],
    ];
   
   $chatId->set($chatData);
   return response()->json([
    'message'=>'Chat Started.',
    'chatID' => $chatId->id(),
    'status' =>Response::HTTP_OK,
  ]);}else{
    $chatDocument = $chatDocuments[0];
    $chatId = $chatDocument->id();
    return response()->json([
        'message'=>'The chat already exists.',
        'chatID' => $chatId,
        'status' =>Response::HTTP_OK,
      ]);
    
  }
    }


// Check if the chat already exists in the local database

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
