<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ApiUser;
use App\Models\Challenge;
use App\Models\ChallengeInvitation;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ChallengesController extends Controller
{
    public function userFootballChallenges()
    {
        $user = Auth::guard('api')->user();
        $results = Challenge::whereHas('users', function ($q) use ($user) {
            $q->where('challenges_api_users.users_id', $user->id);
        })->where('category', 'football')->with('footballResults')->get();

        return response()->json([
            'status' => 'success',
            'data' => $results
        ], Response::HTTP_OK);
    }

    public function userRunningChallenges()
    {
        $user = Auth::guard('api')->user();
        $results = Challenge::whereHas('users', function ($q) use ($user) {
            $q->where('challenges_api_users.users_id', $user->id);
        })->where('category', 'running')->with('runningResults')->get();

        return response()->json([
            'status' => 'success',
            'data' => $results
        ], Response::HTTP_OK);
    }

    public function challengeResults($challenge_id)
    {
        $challenge = Challenge::findOrFail($challenge_id);

        $results = [];

        if ($challenge->category == 'football') {
            $results = $challenge->footballResults()->with(['team1', 'team2', 'team1Award', 'team2Award'])->get();
        } elseif ($challenge->category == 'running') {
            $results = $challenge->runningResults()->with(['team', 'user', 'award'])->get();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Challenge is not of a recognized category.'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => 'success',
            'data' => $results
        ], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => Response::HTTP_UNAUTHORIZED,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'category' => 'required|in:football,running',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'team_id' => 'required|exists:teams,id',
            'invited_team_id' => 'nullable|exists:teams,id|different:team_id',
            'distance' => 'required_if:category,running|numeric',
            'stepsNum' => 'required_if:category,running|numeric',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'opponent_id' => 'nullable|exists:teams,id',
            'image' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => implode(' ', $validator->errors()->all()),
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        try {
            DB::beginTransaction();

            $challengeData = $request->only(['title', 'category', 'latitude', 'longitude', 'start_time', 'end_time']);
            $challenge = Challenge::create($challengeData);
            $team = Team::findOrFail($request->input('team_id'));

            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(public_path('storage/images/challenges'), $imageName);
                $challenge->image = 'images/challenges/' . $imageName;
                $challenge->save();
            }

            if ($request->input('category') === 'football') {
                if ($request->has('invited_team_id')) {
                    ChallengeInvitation::create([
                        'challenge_id' => $challenge->id,
                        'model_type' => Team::class,
                        'model_id' => $request->input('invited_team_id'),
                        'status' => 'pending',
                    ]);
                } else {
                    $teams = Team::where('id', '!=', $request->input('team_id'))->get();
                    foreach ($teams as $team) {
                        ChallengeInvitation::create([
                            'challenge_id' => $challenge->id,
                            'model_type' => Team::class,
                            'model_id' => $team->id,
                            'status' => 'pending',
                        ]);
                    }
                }
            } elseif ($request->input('category') === 'running') {
                $teamUsers = $team->members()->get();
                foreach ($teamUsers as $user) {
                    ChallengeInvitation::create([
                        'challenge_id' => $challenge->id,
                        'model_type' => ApiUser::class,
                        'model_id' => $user->id,
                        'status' => 'pending',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Challenge created successfully',
                'data' => $challenge,
                'status' => Response::HTTP_OK,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create challenge',
                'error' => $e->getMessage(),
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
