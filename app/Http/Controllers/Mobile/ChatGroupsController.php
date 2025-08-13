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

    public function addUsersToGroup(Request $request, $group_id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $chatGroup = ChatGroup::findOrFail($group_id);
        $userRole = $chatGroup->apiUsers()->where('api_user_id', $user->id)->first()->pivot->role ?? null;

        if ($chatGroup->creator->id != $user->id && $userRole !== 'admin') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only the group creator or admins can add users.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:api_users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => implode(' ', $validator->errors()->all())
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $existingUserIds = $chatGroup->apiUsers()->pluck('api_user_id')->toArray();
            $newUserIds = array_diff($request->input('user_ids'), $existingUserIds);

            if (empty($newUserIds)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'All specified users are already participants.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $pivotData = [];
            foreach ($newUserIds as $userId) {
                $pivotData[$userId] = ['role' => 'member'];
            }
            $chatGroup->apiUsers()->attach($pivotData);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Users added to group successfully.',
                'data' => $chatGroup->apiUsers
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error' => $e
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function removeUsersFromGroup(Request $request, $group_id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $chatGroup = ChatGroup::findOrFail($group_id);
        $userRole = $chatGroup->apiUsers()->where('api_user_id', $user->id)->first()->pivot->role ?? null;

        if ($chatGroup->creator->id != $user->id && $userRole !== 'admin') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only the group creator or admins can remove users.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:api_users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => implode(' ', $validator->errors()->all())
            ], Response::HTTP_BAD_REQUEST);
        }

        try {

            DB::beginTransaction();

            $existingUserIds = $chatGroup->apiUsers()->pluck('api_user_id')->toArray();
            $usersIdsToRemove = array_intersect($request->input('user_ids'), $existingUserIds);

            if (empty($usersIdsToRemove)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'None of the specified users are participants.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $userIdsToRemoveWithoutCreator = array_diff($usersIdsToRemove, [$chatGroup->creator->id]);

            $chatGroup->apiUsers()->detach($userIdsToRemoveWithoutCreator);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Users removed from group successfully.',
                'data' => $chatGroup->apiUsers
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error' => $e
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeUserRole(Request $request, $group_id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthenticated.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $chatGroup = ChatGroup::findOrFail($group_id);
        $userRole = $chatGroup->apiUsers()->where('api_user_id', $user->id)->first()->pivot->role ?? null;

        if ($chatGroup->creator->id != $user->id && $userRole !== 'admin') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Only the group creator or admins can change roles.'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:api_users,id',
            'role' => 'required|in:member,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => implode(' ', $validator->errors()->all())
            ], Response::HTTP_BAD_REQUEST);
        }

        try {

            DB::beginTransaction();

            $targetUserId = $request->input('user_id');
            if ($targetUserId == $chatGroup->creator->id) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'The creator role cannot be changed.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $chatGroupUser = $chatGroup->apiUsers()->where('api_user_id', $targetUserId)->first();
            if (!$chatGroupUser) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User is not a participant in this group.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $chatGroup->apiUsers()->updateExistingPivot($targetUserId, ['role' => $request->input('role')]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User role updated successfully.',
                'data' => $chatGroup->apiUsers()->where('api_user_id', $targetUserId)->first()
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error' => $e
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($group_id)
{
    $user = Auth::guard('api')->user();
    if ($user == null) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Unauthenticated.'
        ], Response::HTTP_UNAUTHORIZED);
    }

    $chatGroup = ChatGroup::findOrFail($group_id);
    if ($chatGroup->creator->id != $user->id) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Only the group creator can delete the group.'
        ], Response::HTTP_FORBIDDEN);
    }

    try {
        DB::beginTransaction();

        $chatGroup->apiUsers()->detach(); // Remove all participants
        $chatGroup->delete();

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Group deleted successfully.'
        ], Response::HTTP_OK);
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'error' => $e
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
}
