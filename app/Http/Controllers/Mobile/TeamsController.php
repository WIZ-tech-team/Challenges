<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ApiUser;
use App\Models\Invitation;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class TeamsController extends Controller
{

    public function userTeams()
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }


        $teams = $user->teams()->get();
        $invitations = Invitation::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('team')
            ->get();

        return response()->json([
            'status' => 'success',
            'date' => [
                'teams' => $teams,
                'invitations' => $invitations
            ]
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:football,running',
            'image' => 'nullable|image|max:2048',
            'city' => 'nullable|string|max:255',
            'users_ids' => 'nullable|array',
            'users_ids.*' => 'required|exists:api_users,id|different:' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => implode(' ', $validator->errors()->all()),
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        // Check if user is already enrolled in a team of the same category
        $existingTeams = $user->teams()->where('category', $request->input('category'))->exists();
        if ($existingTeams) {
            return response()->json([
                'message' => 'You are already enrolled in a team of the same category (' . $request->input('category') . '). Only one team per category is allowed.',
                'status' => Response::HTTP_FORBIDDEN,
            ]);
        }

        try {
            DB::beginTransaction();

            $teamData = $request->only(['name', 'category', 'city', 'firebase_document']);
            $team = Team::create($teamData);

            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(public_path('storage/images/teams'), $imageName);
                $team->image = 'storage/images/teams/' . $imageName;
                $team->save();
            }

            $user->teams()->attach($team->id);

            $invitations = [];

            if ($request->has('users_ids')) {

                foreach ($request->input('users_ids') as $user_id) {

                    if ($user_id == $user->id) {
                        continue; // Skip if the user ID is the same as the authenticated user
                    }

                    // no need to check since it is new team
                    // $existingInvitation = Invitation::where('team_id', $team->id)
                    //     ->where('user_id', $user_id)
                    //     ->where('status', 'pending')
                    //     ->first();

                    // if ($existingInvitation) {
                    //     throw new Exception('An invitation is already pending for user ID: ' . $user_id);
                    // }

                    // $user = ApiUser::find($user_id);
                    // if (!$user) {
                    //     throw new Exception('User with ID ' . $user_id . ' not found.');
                    // }

                    // $isUserEnrolled = $user->teams()->where('teams.id', $team->id)->exists();
                    // if ($isUserEnrolled) {
                    //     throw new Exception('User with ID ' . $user_id . ' already enrolled in this team.');
                    // }

                    $invitations[] = Invitation::create([
                        'team_id' => $team->id,
                        'user_id' => $user_id,
                        'status' => 'pending'
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Team created successfully',
                'data' => ['team' => $team, 'invitations' => $invitations],
                'status' => Response::HTTP_OK,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create team',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }

    public function leaveTeam(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'team_id' => 'required|exists:teams,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => implode(' ', $validator->errors()->all()),
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        $team = $user->teams()->where('team_id', $request->input('team_id'))->first();
        if (!$team) {
            return response()->json([
                'message' => 'Team not found, or you are not enrolled in this team.',
                'status' => Response::HTTP_NOT_FOUND,
            ]);
        }

        $user->teams()->detach($team->id);

        if ($user->team_id == $request['team_id']) {
            $user->team_id = null;
        }

        if (!$user->save()) {
            return response()->json([
                'message' => 'Failed to leave team.',
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }

        return response()->json([
            'message' => 'Successfully left the team.',
            'data' => ['team_id' => null],
            'status' => Response::HTTP_OK,
        ]);
    }

    public function membersList($team_id)
    {
        $team = Team::findOrFail($team_id);
        $members = $team->members()->get();

        return response()->json([
            'status' => 'success',
            'data' => $members,
        ], Response::HTTP_OK);
    }

    public function challengesParticipatedInList($team_id)
    {
        $team = Team::findOrFail($team_id);
        $challenges = $team->challengesParticipatedIn()->get();

        return response()->json([
            'status' => 'success',
            'data' => $challenges,
        ], Response::HTTP_OK);
    }
}
