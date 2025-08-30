<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ApiUser;
use App\Models\Invitation;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class InvitationsController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => Response::HTTP_UNAUTHORIZED
            ]);
        }

        $request->validate([
            'status' => 'nullable|in:pending,accepted,refused'
        ]);

        $query = Invitation::query()->where('user_id', $user->id);
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $invitations = $query->with('team')->get();

        return response()->json([
            'message' => 'Invitations retrieved successfully.',
            'data' => $invitations,
            'status' => Response::HTTP_OK
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => Response::HTTP_UNAUTHORIZED
            ]);
        }

        $validator = Validator::make($request->all(), [
            'users_ids' => 'required|array',
            'users_ids.*' => 'required|exists:api_users,id|different:' . $user->id,
            'team_id' => 'required|exists:teams,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => implode(' ', $validator->errors()->all()),
                'status' => Response::HTTP_BAD_REQUEST
            ]);
        }

        $team = $user->teams()->where('team_id', $request['team_id'])->first();
        if (!$team) {
            return response()->json([
                'message' => 'Team not found, or you are not enrolled in this team.',
                'status' => Response::HTTP_NOT_FOUND
            ]);
        }

        $invitations = [];

        foreach ($request->input('users_ids') as $user_id) {
            $existingInvitation = Invitation::where('team_id', $team->id)
                ->where('user_id', $user_id)
                ->where('status', 'pending')
                ->first();

            if ($existingInvitation) {
                return response()->json([
                    'message' => 'An invitation is already pending for user ID: ' . $user_id,
                    'status' => Response::HTTP_BAD_REQUEST
                ]);
            }

            $user = ApiUser::find($user_id);
            if (!$user) {
                return response()->json([
                    'message' => 'User with ID ' . $user_id . ' not found.',
                    'status' => Response::HTTP_BAD_REQUEST
                ]);
            }

            $isUserEnrolled = $user->teams()->where('teams.id', $team->id)->exists();
            if ($isUserEnrolled) {
                return response()->json([
                    'message' => 'User with ID ' . $user_id . ' already enrolled in this team.',
                    'status' => Response::HTTP_BAD_REQUEST
                ]);
            }

            $invitations[] = Invitation::create([
                'team_id' => $team->id,
                'user_id' => $user_id,
                'status' => 'pending'
            ]);
        }

        return response()->json([
            'message' => 'Invitation sent successfully.',
            'data' => $invitations,
            'status' => Response::HTTP_OK
        ]);
    }

    public function resppondToInvitation(Request $request, $invitation_id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => Response::HTTP_UNAUTHORIZED
            ]);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:accept,refuse'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => implode(' ', $validator->errors()->all()),
                'status' => Response::HTTP_BAD_REQUEST
            ]);
        }

        $invitation = Invitation::where('id', $invitation_id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json([
                'message' => 'Invalid or expired invitation.',
                'status' => Response::HTTP_NOT_FOUND
            ]);
        }

        $team = Team::find($invitation->team_id);
        if (!$team) {
            return response()->json([
                'message' => 'Team not found.',
                'status' => Response::HTTP_NOT_FOUND
            ]);
        }

        if ($request->input('action') === 'accept') {
            // Check if user is already enrolled in a team of the same category
            $existingTeam = $user->teams()->where('category', $team->category)->first();

            if ($existingTeam) {
                return response()->json([
                    'message' => 'You are already enrolled in a team of the same category (' . $team->category . '). Leave your team then accept the invitation to be enrolled.',
                    'status' => Response::HTTP_FORBIDDEN
                ]);
            }

            $user->teams()->attach($team->id);

            $invitation->status = 'accepted';
        } else {
            $invitation->status = 'refused';
        }

        $invitation->save();

        return response()->json([
            'message' => 'Invitation ' . ($request->input('action') === 'accept' ? 'accepted' : 'refused') . ' successfully.',
            'data' => $invitation,
            'status' => Response::HTTP_OK
        ]);
    }
}
