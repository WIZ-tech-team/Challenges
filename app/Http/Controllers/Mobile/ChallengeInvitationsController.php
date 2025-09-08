<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ApiUser;
use App\Models\ChallengeInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ChallengeInvitationsController extends Controller
{
    public function userInvitations($status)
    {
        if ($status !== 'pending' && $status !== 'accepted' && $status !== 'refused') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid status. Allowed values are: pending, accepted, refused.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = Auth::guard('api')->user();
        $apiUser = ApiUser::findOrFail($user->id);
        $invitations = $apiUser->challengeInvitations()
            ->with('challenge')
            ->where('status', $status)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $invitations
        ], Response::HTTP_OK);
    }

    public function teamInvitations($status, $team_id)
    {
        try {

            if ($status !== 'pending' && $status !== 'accepted' && $status !== 'refused') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid status. Allowed values are: pending, accepted, refused.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = Auth::guard('api')->user();
            $apiUser = ApiUser::findOrFail($user->id);
            $userLeadTeamsWithInvitations = $apiUser->leadTeams()
                ->with(['challengeInvitations' => function ($query) use ($status) {
                    $query->where('status', $status)->with('challenge');
                }])
                ->where('id', $team_id)
                ->first();

            if (!$userLeadTeamsWithInvitations) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'You are not the leader of this team or the team does not exist.'
                ], Response::HTTP_BAD_REQUEST);
            }

            return response()->json([
                'status' => 'success',
                'data' => $userLeadTeamsWithInvitations
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function respondToInvitation(Request $request, $invitation_id)
    {

        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,refused'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(' ', $validator->errors()->all())
            ], Response::HTTP_BAD_REQUEST);
        }

        $apiUser = ApiUser::findOrFail($user->id);
        $team = null;
        $invitation = ChallengeInvitation::findOrFail($invitation_id);
        $challenge = $invitation->challenge;

        if ($invitation->status != 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'This invitation has already been responded to.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($challenge->category === 'football') {
            $team = $apiUser->leadTeams()->where('category', 'football')->first();
            if (!$team) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You must be a leader of football team to accept this invitation.'
                ], Response::HTTP_BAD_REQUEST);
            }
            if ($request['status'] === 'accepted') {
                $invitation->status = $request->input('status');
                $invitation->save();
                $team->challengesParticipatedIn()->attach($challenge->id);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Invitation accepted successfully.'
                ], Response::HTTP_OK);
            } else {
                $invitation->status = 'refused';
                $invitation->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Invitation refused successfully.'
                ], Response::HTTP_OK);
            }
        }

        if ($challenge->category === 'running') {
            if ($request['status'] === 'accepted') {
                $invitation->status = $request->input('status');
                $invitation->save();
                $apiUser->challenges()->attach($challenge->id);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Invitation accepted successfully.'
                ], Response::HTTP_OK);
            } else {
                $invitation->status = 'refused';
                $invitation->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Invitation refused successfully.'
                ], Response::HTTP_OK);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Invitation ' . $request->input('status') . ' successfully.'
        ], Response::HTTP_OK);
    }
}
