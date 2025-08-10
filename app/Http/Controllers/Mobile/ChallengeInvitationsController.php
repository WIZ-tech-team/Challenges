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
    public function index($status)
    {
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

    public function respondToInvitation(Request $request, $invitation_id)
    {

        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $apiUser = ApiUser::findOrFail($user->id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,refused'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => implode(' ', $validator->errors()->all())
            ], Response::HTTP_BAD_REQUEST);
        }

        $invitation = $apiUser->challengeInvitations()->findOrFail($invitation_id);

        $invitation->status = $request->input('status');
        $invitation->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Invitation ' . $request->input('status') . ' successfully.'
        ], Response::HTTP_OK);
    }
}
