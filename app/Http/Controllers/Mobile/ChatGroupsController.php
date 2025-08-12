<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ApiUser;
use App\Models\ChatGroup;
use App\Models\ChatGroupApiUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ChatGroupsController extends Controller
{

    public function index()
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $apiUser = ApiUser::findOrFail($user->id);

        $groups = $apiUser->participatedChatGroups()->with('apiUsers:id,name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $groups
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $apiUser = ApiUser::findOrFail($user->id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|image|max:2048',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:api_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => implode(' ', $validator->errors()->all())
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $chatGroup = ChatGroup::create([
                'name' => $request->input('name'),
                'created_by' => $apiUser->id,
                'status' => 'active',
            ]);

            if ($request->hasFile('avatar')) {
                $avatarName = time() . '.' . $request->file('avatar')->getClientOriginalExtension();
                $request->file('avatar')->move(public_path('storage/images/groups/avatars'), $avatarName);
                $chatGroup->avatar = 'storage/images/groups/avatars/' . $avatarName;
                $chatGroup->save();
            }

            $participantIds = isset($request['participant_ids']) ?
                array_unique(array_merge([$apiUser->id], $request->input('participant_ids', []))) :
                [$apiUser->id];

            foreach ($participantIds as $participantId) {
                $pivotData[$participantId] = ['role' => $participantId === $apiUser->id ? 'admin' : 'member'];
            }
            $chatGroup->apiUsers()->attach($pivotData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Group created successfully.',
                'data' => $chatGroup
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
