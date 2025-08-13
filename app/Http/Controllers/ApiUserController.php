<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Http;
use Kreait\Firebase\Exception\Auth\PhoneNumberAlreadyExists;
use PHPSupabase\Service;

class ApiUserController extends Controller
{

    protected $supabase;

    public function __construct()
    {
        $this->supabase = new Service(
            config('supabase.key'),
            config('supabase.url') . '/rest/v1'
        );
    }

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

        return in_array($domain, $allowedDomains) || true;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', Rules\Password::defaults()],
            'confirm_password' => ['required', 'same:password'],
            'phone' => ['required', 'string', 'max:255', 'regex:/^\+[0-9]+$/'],
        ]);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            $formattedErrorMessages = implode(' ', $errorMessages);
            return response()->json([
                'message' => $formattedErrorMessages,
                'status' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $phoneNumber = $request->input('phone');

        if (!$this->isValidEmailDomain($email)) {
            return response()->json([
                'message' => 'Invalid email domain',
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $phoneNumberObj = $phoneNumberUtil->parse($phoneNumber);
        if (!$phoneNumberUtil->isValidNumber($phoneNumberObj)) {
            return response()->json([
                'message' => 'Invalid phone number',
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        $formattedPhoneNumber = $phoneNumberUtil->format($phoneNumberObj, PhoneNumberFormat::E164);

        // Check if phone or email exists in Supabase users table
        $db = $this->supabase->initializeDatabase('Authorization', config('supabase.key'));
        $existingPhone = $db->createCustomQuery([
            'from' => 'users',
            'select' => '*',
            'where' => ['phone' => 'eq.' . $formattedPhoneNumber]
        ])->getResult();
        if (!empty($existingPhone)) {
            return response()->json([
                'message' => 'Phone already registered',
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        $existingEmail = $db->createCustomQuery([
            'from' => 'users',
            'select' => '*',
            'where' => ['email' => 'eq.' . $email]
        ])->getResult();
        if (!empty($existingEmail)) {
            return response()->json([
                'message' => 'Email already registered',
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        try {
            // Create user in Supabase Auth
            $auth = $this->supabase->createAuth();
            $user_metadata = ['name' => $name, 'phone' => $formattedPhoneNumber];
            $response = $auth->createUserWithPhoneAndPassword($formattedPhoneNumber, $password, $user_metadata);

            if ($auth->getError()) {
                return response()->json([
                    'message' => $auth->getError(),
                    'status' => Response::HTTP_BAD_REQUEST,
                ]);
            }

            $userData = $auth->data();
            $supabaseUser = $userData->user;

            // Store user data in Laravel's ApiUser model
            $apiUser = new ApiUser();
            $apiUser->name = $name;
            $apiUser->email = $email;
            $apiUser->password = Hash::make($password);
            $apiUser->phone = $formattedPhoneNumber;
            $apiUser->save();

            // // Store user data in Supabase users table
            // // In store method, replace the insert call:
            // $db->insert([
            //     'id' => (string) Str::uuid(),
            //     'name' => $name,
            //     'email' => $email,
            //     'password' => Hash::make($password),
            //     'phone' => $formattedPhoneNumber,
            // ], 'users');

            return response()->json([
                'message' => 'User added successfully',
                'data' => $apiUser,
                'status' => Response::HTTP_OK,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to store user',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

    }

    // public function store(Request $request)
    // {   $validator = Validator::make($request->all(),[
    //     'name'     =>  ['required', 'string', 'max:255'],
    //     'phone'    =>  ['required', 'string', 'max:255'],
    //     'email'    =>  ['required', 'email', 'max:255'],
    //     'password' =>  ['required', Rules\Password::defaults()],
    //     'confirm_password' => ['required', 'same:password'],
    // ], );

    // if ($validator->fails()) {
    //     $errorMessages = $validator->errors()->all();
    //     $formattedErrorMessages = implode(' ', $errorMessages);

    //     return response()->json([
    //         'message' => $formattedErrorMessages,
    //         'status'  => Response::HTTP_BAD_REQUEST,
    //     ], Response::HTTP_BAD_REQUEST);
    // }



    //     $serviceAccount = ServiceAccount::fromValue(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
    //     $firebase       = (new Factory)->withServiceAccount(public_path('challenge-88-firebase-adminsdk-7plca-d3ba680858.json'));
    //     $name           = $request->input('name');
    //     $phoneNumber    = $request->input('phone');
    //     $password       = $request->input('password');
    //     $email          = $request->input('email');

    //     $firebaseAuth = app(AuthFirebase::class);
    //     if (!$this->isValidEmailDomain($email)) {
    //         return response()->json([
    //             'message' => 'Invalid email domain',
    //             'status' => Response::HTTP_BAD_REQUEST,
    //         ]);
    //     }
    //    $phoneNumberUtil = PhoneNumberUtil::getInstance();
    //    $phoneNumberObj = $phoneNumberUtil->parse($phoneNumber, 'PS');

    //    if (!$phoneNumberUtil->isValidNumber($phoneNumberObj)) {
    //       return (['message' =>'Invalid phone number', 'status' => Response::HTTP_BAD_REQUEST,]);
    //    }

    //    $formattedPhoneNumber = $phoneNumberUtil->format($phoneNumberObj, PhoneNumberFormat::E164);
    //    $userProperties = [

    //     'password' => $password ,
    //     'phone'    => $formattedPhoneNumber,
    // ];
    // $existingUserByEmail = ApiUser::where('email', $email)->first();

    // if ($existingUserByEmail) {
    //     return response()->json([
    //         'message' => 'Email already registered',
    //         'status'  => Response::HTTP_BAD_REQUEST,
    //     ]);
    // }
    // try {
    //     $existingUser = $firebaseAuth->getUserByPhoneNumber($formattedPhoneNumber);
    //     // If the email is already registered, return an error
    //     return response()->json([
    //         'message' => 'Phone already registered',
    //         'status'  => Response::HTTP_BAD_REQUEST,
    //     ]);


    // } catch (\Kreait\Firebase\Exception\Auth\UserNotFound $e) {
    //     // User not found, continue with registration
    // }

    // $firebaseAuth = app(AuthFirebase::class);

    //     $user1 = $firebaseAuth->createUser($userProperties);

    //     $apiUser = new ApiUser();
    //     $apiUser->name         = $request->post('name');
    //     $apiUser->phone        = $phoneNumber;
    //     $apiUser->email        = $email;
    //     $apiUser->password     = Hash::make($password);
    //    // $apiUser->api_token    = Str::random(60);
    //     $apiUser->firebase_uid = $user1->uid;

    //     $firestore = $firebase->createFirestore();
    //     $database = $firestore->database();
    //     $usersCollection = $database->collection('Users');
    //     $usersCollection->document($apiUser->firebase_uid)->set([
    //      'name'         => $apiUser->name,
    //      'email'        => $apiUser->email,
    //      'phone'        => $apiUser->phone,
    //      'password'     => $apiUser->password,
    //      'firebase_uid' => $apiUser->firebase_uid,
    //      'fcm_token'    => $apiUser->fcm_token,

    //      // Add other user data fields as needed
    //  ]);
    //     $apiUser->save();
    //     return response()->json([
    //         'message' => 'User added successfully',
    //         'data'    => $apiUser,
    //         'status'  => Response::HTTP_OK
    //     ]
    //     );

    // }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:255', 'regex:/^\+[0-9]+$/'],
            'password' => ['required', 'string'],
            'fcm_token' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => implode(' ', $validator->errors()->all()),
                'status' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }

        $phone = $request->input('phone');
        $password = $request->input('password');
        $fcmToken = $request->input('fcm_token');

        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneNumberObj = $phoneNumberUtil->parse($phone);
            $formattedPhone = $phoneNumberUtil->format($phoneNumberObj, PhoneNumberFormat::E164);
        } catch (\libphonenumber\NumberParseException $e) {
            return response()->json([
                'message' => 'Invalid phone number format',
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        try {
            $fields = [
                'phone' => $formattedPhone,
                'password' => $password,
            ];
            $uri = $this->supabase->getUriBase('auth/v1/token?grant_type=password');
            $options = [
                'headers' => $this->supabase->getHeaders(),
                'body' => json_encode($fields),
            ];
            $response = $this->supabase->executeHttpRequest('POST', $uri, $options);

            if ($this->supabase->getError()) {
                return response()->json([
                    'message' => $this->supabase->getError(),
                    'status' => Response::HTTP_UNAUTHORIZED,
                ]);
            }

            // $userData = json_decode($response);
            // $supabaseUser = $userData->user ?? $userData; // Adjust based on response structure
            $user = ApiUser::where('phone', ltrim($formattedPhone, '+'))->first();

            if (!$user) {
                $user = new ApiUser();
                $user->phone = ltrim($formattedPhone, '+');
                $user->password = Hash::make($password);
                $user->name = $response->user->user_metadata->name;
                $user->email = $response->user->email;
                $user->save();
            }

            $apiToken = Str::random(60);
            $user->fcm_token = $fcmToken;
            $user->api_token = $apiToken;
            $user->save();

            return response()->json([
                'message' => 'User login successfully.',
                'data' => $user,
                'status' => Response::HTTP_OK,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to login',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }
    }
    // public function login(Request $request)
    // {

    //     // $credentials = [
    //     //     'password' => $request->password,
    //     // ];

    //     // // Check if the request contains the 'email' field
    //     // if ($request->filled('email')) {
    //     //     $credentials['email'] = $request->email;
    //     // } elseif ($request->filled('phone')) {
    //     //     $credentials['phone'] = $request->phone;
    //     // } else {
    //     //     // If neither email nor phone is provided, return an error response
    //     //     return response()->json([
    //     //         'message' => 'Please provide either email or phone for login.',
    //     //         'status'  => Response::HTTP_BAD_REQUEST,
    //     //     ]);
    //     // }
    //     // $uid = $request->post('firebase_uid');
    //     //     $credentials = [ 
    //     //         'firebase_uid' => $request->uid ,
    //     //         'password' => $request->password,];
    //     //   $user =Auth::guard('api')->getProvider()->retrieveByCredentials($credentials);

    //     //         if (!$user) {
    //     //             return response()->json([
    //     //                 'message' => 'User not found',
    //     //                 'status'  => Response::HTTP_NOT_FOUND,
    //     //             ]);
    //     //         }

    //     //         if (!Auth::guard('api')->getProvider()->validateCredentials($user, $credentials)) {
    //     //             return response()->json([
    //     //                 'message' => 'Incorrect password',
    //     //                 'status'  => Response::HTTP_UNAUTHORIZED,
    //     //             ]);
    //     //         }
    //     $firebase_uid = $request->uid;
    //     $fcmToken = $request->fcm_token;
    //     $user = ApiUser::where('firebase_uid', $firebase_uid)->first();
    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'User not found',
    //             'status'  => Response::HTTP_NOT_FOUND,
    //         ]);
    //     }
    //     $apiToken        = Str::random(60);
    //     $user->fcm_token = $fcmToken;
    //     $user->api_token = $apiToken;
    //     $user->save();

    //     return response()->json([
    //         'message' => 'User login successfully.',
    //         'data' => $user,
    //         'status'  => Response::HTTP_OK
    //     ]);
    // }
    /************************************** */

    public function register1(Request $request)
    {
        $validatedData = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'phone'            => ['required', 'string', 'max:255', 'unique:api_users'],
            'email'            => ['required', 'email', 'max:255', 'unique:api_users'],
            'password'         => ['required', Rules\Password::defaults()],
            'confirm_password' => ['required', 'same:password'],


        ]);

        $email = $validatedData['email'];
        $password = $validatedData['password'];
        $phone = $validatedData['phone'];

        // Create a new user with Firebase Auth
        $firebaseAuth = app(AuthFirebase::class);
        $user =  $firebaseAuth->createUserWithEmailAndPassword($email, $password, $phone);
        dd($user);
        return response()->json([
            'message' => 'User registered successfully!',
            'user' => $user,
        ], 201);
    }


    public function login1(Request $request)
    {
        $firebaseAuth = app(AuthFirebase::class);
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $email = $validatedData['email'];
        $password = $validatedData['password'];


        try {
            $user = $firebaseAuth->signInWithEmailAndPassword($email, $password);


            $idToken = $user->idToken();
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
            'data' => $users,
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
    public function Points(User $user)
    {
        // Get the user's team_id
        $teamId = $user->team_id;

        $challengesCount = Challenge::where('team_id', $teamId)
            ->whereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        // Calculate field points based on your logic, for example:
        $fieldPoints = $challengesCount * 10; // You can adjust this formula as needed

        return $fieldPoints;

        // Example usage:
        $user = User::find(1); // Replace with the user you want to calculate field points for
        $fieldPoints = calculateFieldPoints($user);
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
        $validator = Validator::make($request->all(), [
            'email'    =>  ['required', 'email', 'max:255'],
        ]);
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
            $user['avatar'] = $file->store('/avatars', 'public');
        }

        $firestore       = $firebase->createFirestore();
        $database        = $firestore->database();
        $usersCollection = $database->collection('Users');
        $usersCollection->document($id)->update(
            [
                ['path' => 'name',   'value' => $name],
                ['path' => 'avatar', 'value' => $user->avatar],

            ]
        );

        $user->save();

        return response()->json([
            'message' => 'User update succesfully',
            'data'    => $user,
            'status'  => Response::HTTP_OK
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
        $user = Auth::guard('api')->user();
        $action = $request->input('action');
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  => Response::HTTP_NOT_FOUND,
            ]);
        }
        if ($action === 'leave') {
            $user->team_id = null;
            $user->type = 'member';
        }
        $user->save();
        return response()->json([
            'message' => 'User leaved the team',
            'status' => Response::HTTP_OK,
        ]);
    }

    public function getAllUsers()
    {
        $user    = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  => Response::HTTP_NOT_FOUND,
            ]);
        }
        $q = $user->load('team');
        return response()->json([
            'message' => 'User profile here',
            'user_data' => $q,
            'status' => Response::HTTP_OK,
        ], 200);
    }


    public function addDataToFirestore()
    {
        $firebase =
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

    public function contactTest(Request $request)
    {
        $validator = Validator::make($request->all(), [


            'phones' => 'required',

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
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  => Response::HTTP_NOT_FOUND,
            ]);
        }
        $phonesInput = $request->post('phones');
        $phonesToAdd = explode(',', $phonesInput);

        if ($user->contacts !== null) {
            $previousContacts = json_decode($user->contacts, true);

            $existingContacts = array_intersect($phonesToAdd, $previousContacts);

            $phonesToAdd = array_diff($phonesToAdd, $previousContacts);


            $newContacts = array_merge($previousContacts, $phonesToAdd);
            $user->contacts = $newContacts;
        } else {
            $user->contacts = $phonesToAdd;
            $existingContacts = [];
        }

        $user->save();

        return response()->json([
            'message' => 'Contacts updated successfully',
            'existing_contacts' => $existingContacts,
            'status' => Response::HTTP_OK,
        ]);
    }

    //  $serviceAccount = ServiceAccount::fromValue(public_path('treeme-chat-firebase-adminsdk-w20bj-0ea0723bc2.json'));
    //  $firebase = (new Factory)
    //              ->withServiceAccount($serviceAccount);
    //  $firestore = $firebase->createFirestore();
    //  $database = $firestore->database();
    //  $usersCollection = $database->collection('Users');
    //  $userDocument = $usersCollection->document($user->firebase_uid);
    //  $userDocument->update([
    //    ['path' => 'contact', 'value' => $phones],]);








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
    public function refreshToken(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  => Response::HTTP_NOT_FOUND,
            ]);
        }
        $fcmToken = $request->input('fcm_token');
        $user->fcm_token = $fcmToken;
        $user->save();
        return response()->json([
            'message' => 'fcm token refreshed',
            'status'  => Response::HTTP_OK,
        ]);
    }
}
