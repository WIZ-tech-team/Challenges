<?php

namespace App\Http\Controllers;
require 'C:\\xampp\\htdocs\\SportsApp\\vendor\\autoload.php';

use app;
use App\Models\ApiUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Http\Response;
use Kreait\Firebase\Database;
use Kreait\Firebase\Auth as A;
use Kreait\Firebase\Firestore;
use Kreait\Firebase\JWT\Token;
use Illuminate\Validation\Rules;
use Kreait\Firebase\ServiceAccount;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use libphonenumber\PhoneNumberFormat;
use Firebase\Firestore\FirebaseFirestore;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Auth\SignIn\PhoneNumber;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Google\Cloud\Firestore\CollectionReference;
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Auth\SignIn\PhoneSignInResult;
use Kreait\Firebase\Contract\Auth as AuthFirebase;
use Kreait\Firebase\Messaging\WebPushNotification;
use Kreait\Firebase\Exception\Auth\PhoneNumberExists;
use Kreait\Firebase\Exception\Auth\InvalidPhoneNumber;
use Google\Cloud\Firestore\Connection\ConnectionInterface;
use Kreait\Firebase\Exception\Auth\PhoneNumberAlreadyExists;

class ApiUserController extends Controller
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
    private function isValidEmailDomain($email)
    {
        // You can implement your domain validation logic here
        // For example, check if the email's domain is from hotmail or gmail
        $allowedDomains = ['hotmail.com', 'gmail.com'];

        $domain = explode('@', $email)[1];

        return in_array($domain, $allowedDomains);
    }

    public function store(Request $request)
    {   $validator = Validator::make($request->all(),[
        'name'     =>  ['required', 'string', 'max:255'],
        'phone'    =>  ['required', 'string', 'max:255'],
        'email'    =>  ['required', 'email', 'max:255'],
        'password' =>  ['required', Rules\Password::defaults()],
        'confirm_password' => ['required', 'same:password'],
    ], );
    
    if ($validator->fails()) {
        $errorMessages = $validator->errors()->all();
        $formattedErrorMessages = implode(' ', $errorMessages);

        return response()->json([
            'message' => $formattedErrorMessages,
            'status'  => Response::HTTP_BAD_REQUEST,
        ], Response::HTTP_BAD_REQUEST);
    }

    
    
        $serviceAccount = ServiceAccount::fromValue(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
        $firebase       = (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
        $name           = $request->input('name');
        $phoneNumber    = $request->input('phone');
        $password       = $request->input('password');
        $email          = $request->input('email');
       
        $firebaseAuth = app(AuthFirebase::class);
        if (!$this->isValidEmailDomain($email)) {
            return response()->json([
                'message' => 'Invalid email domain',
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }
       $phoneNumberUtil = PhoneNumberUtil::getInstance();
       $phoneNumberObj = $phoneNumberUtil->parse($phoneNumber, 'PS');

       if (!$phoneNumberUtil->isValidNumber($phoneNumberObj)) {
          return (['message' =>'Invalid phone number', 'status' => Response::HTTP_BAD_REQUEST,]);
       }

       $formattedPhoneNumber = $phoneNumberUtil->format($phoneNumberObj, PhoneNumberFormat::E164);
       $userProperties = [
        
        'password' => $password ,
        'phone'    => $formattedPhoneNumber,
    ];
    $existingUserByEmail = ApiUser::where('email', $email)->first();
       
    if ($existingUserByEmail) {
        return response()->json([
            'message' => 'Email already registered',
            'status'  => Response::HTTP_BAD_REQUEST,
        ]);
    }
    try {
        $existingUser = $firebaseAuth->getUserByPhoneNumber($formattedPhoneNumber);
        // If the email is already registered, return an error
        return response()->json([
            'message' => 'Phone already registered',
            'status'  => Response::HTTP_BAD_REQUEST,
        ]);

      
    } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
        // User not found, continue with registration
    }
 
    $firebaseAuth = app(AuthFirebase::class);
 
        $user1 = $firebaseAuth->createUser($userProperties);
   
        $apiUser = new ApiUser();
        $apiUser->name         = $request->post('name');
        $apiUser->phone        = $phoneNumber;
        $apiUser->email        = $email;
        $apiUser->password     = Hash::make($password);
       // $apiUser->api_token    = Str::random(60);
        $apiUser->firebase_uid = $user1->uid;

        $firestore = $firebase->createFirestore();
        $database = $firestore->database();
        $usersCollection = $database->collection('Users');
        $usersCollection->document($apiUser->firebase_uid)->set([
         'name'         => $apiUser->name,
         'email'        => $apiUser->email,
         'phone'        => $apiUser->phone,
         'password'     => $apiUser->password,
         'firebase_uid' => $apiUser->firebase_uid,
         'fcm_token'    => $apiUser->fcm_token,
        
         // Add other user data fields as needed
     ]);
        $apiUser->save();
        return response()->json([
            'message' => 'User added successfully',
            'data'    => $apiUser,
            'status'  => Response::HTTP_OK
        ]
        );
                
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request){
        
        // $credentials = [
        //     'password' => $request->password,
        // ];
    
        // // Check if the request contains the 'email' field
        // if ($request->filled('email')) {
        //     $credentials['email'] = $request->email;
        // } elseif ($request->filled('phone')) {
        //     $credentials['phone'] = $request->phone;
        // } else {
        //     // If neither email nor phone is provided, return an error response
        //     return response()->json([
        //         'message' => 'Please provide either email or phone for login.',
        //         'status'  => Response::HTTP_BAD_REQUEST,
        //     ]);
        // }
       // $uid = $request->post('firebase_uid');
    //     $credentials = [ 
    //         'firebase_uid' => $request->uid ,
    //         'password' => $request->password,];
    //   $user =Auth::guard('api')->getProvider()->retrieveByCredentials($credentials);
  
    //         if (!$user) {
    //             return response()->json([
    //                 'message' => 'User not found',
    //                 'status'  => Response::HTTP_NOT_FOUND,
    //             ]);
    //         }
        
    //         if (!Auth::guard('api')->getProvider()->validateCredentials($user, $credentials)) {
    //             return response()->json([
    //                 'message' => 'Incorrect password',
    //                 'status'  => Response::HTTP_UNAUTHORIZED,
    //             ]);
    //         }
    $firebase_uid=$request->uid;
    $user = ApiUser::where('firebase_uid', $firebase_uid)->first();
            $apiToken = Str::random(60); 
            $user->api_token = $apiToken;
            $user->save();

          return response()->json(['message' =>'User login successfully.',
          'data'=> $user,
          'status'  =>Response::HTTP_OK
           ]);}
           /************************************** */
       
           public function register1(Request $request){
            $validatedData = $request->validate([
                'name'             => ['required', 'string', 'max:255'],
                'phone'            => ['required', 'string', 'max:255','unique:api_users'],
                'email'            => ['required', 'email', 'max:255','unique:api_users'],
                'password'         => ['required', Rules\Password::defaults()],
                'confirm_password' => ['required','same:password'],

              
            ]);
    
            $email = $validatedData['email'];
            $password = $validatedData['password'];
            $phone = $validatedData['phone'];
    
            // Create a new user with Firebase Auth
            $firebaseAuth = app(AuthFirebase::class);
            $user =  $firebaseAuth->createUserWithEmailAndPassword($email, $password,$phone);
          dd($user);
            return response()->json([
                'message' => 'User registered successfully!',
                'user' => $user,
            ], 201);
           }


      public function login1(Request $request){
        $firebaseAuth = app(AuthFirebase::class);
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
       
        $email = $validatedData['email'];
        $password = $validatedData['password'];
   
   
        try {
            $user = $firebaseAuth->signInWithEmailAndPassword($email, $password);
    
        
            $idToken =$user->idToken();
            $verifiedIdToken = $firebaseAuth->verifyIdToken($idToken);
           
            if (!$verifiedIdToken) {
                return response()->json([
                    'message' => 'User Not Authenticated!',
                    'status' => Response::HTTP_NOT_FOUND,
                ], Response::HTTP_NOT_FOUND);
            }
            // $refreshToken = $user->refreshToken();
            // $newIdToken = $firebaseAuth->signInWithRefreshToken($refreshToken);
         
            return response()->json([
                'message' => 'User logged in successfully!',
                'data' => $user->data(),
                'status' => Response::HTTP_OK,
            ], Response::HTTP_OK);
        } catch (FailedToSignIn $e) {
            return response()->json([
                'message' => $e->getMessage(),
               
                'status' => Response::HTTP_UNAUTHORIZED,
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
     
    public function show()
    {
        $users = ApiUser::all();
        return response()->json([
               'message' => 'All Users Here',
                'data'=>$users,
                'status' => Response::HTTP_OK,
        ]);

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
    {   $validator = Validator::make($request->all(), [
        'email'    =>  ['required', 'email', 'max:255'],]);
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            $formattedErrorMessages = implode(' ', $errorMessages);
    
            return response()->json([
                'message' => $formattedErrorMessages,
                'status'  => Response::HTTP_BAD_REQUEST,
            ]);
        }
        $firebase  = (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
        $userApi   = ApiUser::where('firebase_uid', $id)->first();
       
        if (!$userApi) {
            return response()->json([
                'message' => 'User not found',
                'status' => Response::HTTP_NOT_FOUND
            ]);
        }
        $userId       = $userApi->id;
        $user         = ApiUser::find($userId);
        $user->avatar = $request->post('avatar');
        $user->email = $request->post('email');

        $name         = $request->post('name');
        $user->name   = $name;

         
       if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $user['avatar'] = $file->store('/avatars' , 'public');
        }
         
       $firestore       = $firebase->createFirestore();
       $database        = $firestore->database();
       $usersCollection = $database->collection('Users');
       $usersCollection->document($id)->update(
       [
        ['path' => 'name',   'value' => $name],
        ['path' => 'avatar', 'value' => $user->avatar],
       
       ]);
        
       $user->save();

        return response()->json([
            'message' =>'User update succesfully',
            'data'    =>$user,
            'status'  =>Response::HTTP_OK
        ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user =Auth::guard('api')->user();
        $action = $request->input('action');
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  =>Response::HTTP_NOT_FOUND,
            ]);
        }
        if ($action === 'leave') {
            $user->team_id = null;
        $user->type= 'member';}
$user->save();
            return response()->json([
            'message'=>'User leaved the team',
            'status'=>Response::HTTP_OK,
            ]);
    }

    public function getAllUsers($token){
      $firebaseAuth = app(AuthFirebase::class);
      $user         = $firebaseAuth->getUser($token);
      $apiUser      = ApiUser::where('firebase_uid',$token)->first();
    
      return response()->json([
        'user_data' =>['name'=>$apiUser->name,
                      'avatar'=>$apiUser->avatar, 
                      'points'=>$apiUser->points] ,
        'challenges'=>'challenges'       ,   
      ]);
    }    
    
    
    public function addDataToFirestore()
{  $firebase =
    (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'))
    ->withDatabaseUri('https://challenge-88-default-rtdb.firebaseio.com');
    $database = $firebase->createDatabase();

    // Get a reference to the root of your database
  $reference = $database->getReference();

    // Data to be added to the database
    $data = [
        'name' => 'John 123',
        'email' => 'johndoe@example.com',
        // Add other fields as needed
    ];

    // Set the data in the Realtime Database under a specific node (e.g., 'Users')
   // $reference->getChild('Users')->set($data);
    $reference = $database->getReference('Users');

    $reference->getChild('Users')->on('value', function ($snapshot) {
        // This callback will be triggered whenever data changes in the 'Users' node
    
        // Get the updated data from the snapshot
        $updatedData = $snapshot->getValue();
    
        // Handle the updated data as per your requirements
        // For example, you can log, process, or broadcast the data to other connected clients here
    
        // To verify that the real-time listener is working, you can print the updated data
        dd($updatedData);
    });

    
}
 
public function contactTest(Request $request ){
    $validator = Validator::make($request->all(),[
  //  $validator = $request->validate(  [

        'phones'=> 'required',
   
    ]);
    
 
    if ($validator->fails()) {
        $errorMessages = $validator->errors()->all();
        $formattedErrorMessages = implode(' ', $errorMessages);

        return response()->json([
            'message' => $formattedErrorMessages,
            'status'  => Response::HTTP_BAD_REQUEST,
        ], Response::HTTP_BAD_REQUEST);
    }
      
     $user  = Auth::guard('api')->user();
     $phonesInput = $request->post('phones'); 
     $phones = explode(',', $phonesInput);
     $previousContacts = json_decode($user->contacts, true);
     $phones = array_merge($previousContacts, $phones);
    
   //  $serviceAccount = ServiceAccount::fromValue(public_path('treeme-chat-firebase-adminsdk-w20bj-0ea0723bc2.json'));
    //  $firebase = (new Factory)
    //              ->withServiceAccount($serviceAccount);
    //  $firestore = $firebase->createFirestore();
    //  $database = $firestore->database();
    //  $usersCollection = $database->collection('Users');
    //  $userDocument = $usersCollection->document($user->firebase_uid);
    //  $userDocument->update([
    //    ['path' => 'contact', 'value' => $phones],]);
     $user->contacts= $phones ;
     $user->save();
  
 


return response()->json([
  'message'=>'Contact added successfully',
  
  'status'=>Response::HTTP_OK,]);
   
}

public function search(Request $request)
{  
    
   // Get the authenticated user
   $user = Auth::guard('api')->user();

   // Check if the authenticated user has the 'contacts' field (JSON-encoded array of phone numbers)
   if (!$user || !isset($user->contacts)) {
       return response()->json(['message' => 'User contacts not available'], 400);
   }
   $userContactsArray = json_decode($user->contacts, true);

   $matchedUsers = ApiUser::whereIn('phone', $userContactsArray)->get();
   $searchQuery = $request->input('query');
   $searchQueryPartial = "%$searchQuery%";

   $finalMatchedUsers = $matchedUsers->filter(function ($user) use ($searchQuery) {
    $r1 = str_contains(strtolower($user->phone), strtolower($searchQuery));
    $r2 = str_contains(strtolower($user->name), strtolower($searchQuery));
    return json_decode($r1 || $r2);
});

$mergedResponse = [];
foreach ($finalMatchedUsers as $user) {
    $combinedResponse = $user;
    $mergedResponse[] = $combinedResponse;
}



    
    return response()->json([
        'message' => 'The searched user contacts:',
        'data' => $mergedResponse,
        'status' => Response::HTTP_OK,
    ]);

    }
}
