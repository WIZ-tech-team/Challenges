<?php

namespace App\Http\Controllers;

use App\Models\ApiUser;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ContactsController extends Controller
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
        $user = Auth::guard('api')->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  =>Response::HTTP_NOT_FOUND,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'First-Name' => 'required|string|max:255',
            'Last-Name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:255', 'regex:/^\+[0-9]+$/',
                Rule::unique('contacts', 'phone')->where(function ($query) use ($user) {
                    return $query->where('user_id', $user->id);
                }
            )
        ]
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'messages' => implode(' ', $validator->errors()->all())
            ], HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        $phonesInput = $request->post('phone'); 
        $userWithPhone = ApiUser::where('phone', $phonesInput)->first();

        if ($userWithPhone) {
        $contact = new Contact();
        $contact->FirstName = $request->input('First-Name');
        $contact->LasstName = $request->post('Last-Name');
        $contact->phone =   $phonesInput ;
        $contact->user_id = $user->id;
       

     //   $phonesInput = $request->post('phone'); 

        $phones = explode(',', $phonesInput);
        $previousContacts = json_decode($user->contacts, true);
        $phones = array_merge($previousContacts ?? [], $phones ?? []);

        $user->contacts= $phones ;
        $contact->save();
        $user->save();
        return response()->json([
            'message' => 'Contact added successfully.',
            'contact_data'=>$contact,
            'status'=>Response::HTTP_OK]);
    
        } else {
           return response()->json(['message' => 'Phone number not found as a user',
           'status'=> Response::HTTP_BAD_REQUEST]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
                'status'  =>Response::HTTP_NOT_FOUND,
            ]);
        }
        $contacts = $user->contacts;
        $contact = json_decode($contacts);
        $users= ApiUser::all();
        $output = [];

        if($contact) {
            foreach ($contact as $phone) {
                $found = false;
        
                foreach ($users as $user) {
                    if ($phone === $user->phone) {
                        $found = true;
                        break;
                    } 
                }
                $a =ApiUser::where('phone', $phone)->first();
                $status = $found ? 'found' : 'not found';
                $user_id = $found ? $a : null;
                $output[] = [
                'phone' => $phone,
                'status' => $status,
                'user_data' =>$user_id,
                ];
            }
        }

        return response()->json(['message' => 'Contacts',
        'data'  => $output,
        'status'=> Response::HTTP_OK]);
  
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
