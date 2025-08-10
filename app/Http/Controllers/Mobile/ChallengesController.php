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

        $apiUser = ApiUser::findOrFail(12);
        $team = $apiUser->team()->first();
        $category = $team->category;

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'distance' => 'required_if:category,running|numeric',
            'stepsNum' => 'required_if:category,running|numeric',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'opponent_id' => 'nullable|exists:teams,id',
            'image' => 'nullable|image|max:2048'
        ], [
            'distance.required_if' => 'The distance field is required for running challenges.',
            'stepsNum.required_if' => 'The stepsNum field is required for running challenges.',
        ]);

        // Replace 'category' with the team's category in validation conditions
        $validator->sometimes('distance', 'required|numeric', function () use ($category) {
            return $category === 'running';
        });
        $validator->sometimes('stepsNum', 'required|numeric', function () use ($category) {
            return $category === 'running';
        });
        $validator->sometimes('opponent_id', 'nullable|exists:teams,id|different:team_id', function () use ($category) {
            return $category === 'football';
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => implode(' ', $validator->errors()->all()),
                'status' => Response::HTTP_BAD_REQUEST,
            ]);
        }

        try {
            DB::beginTransaction();

            $challengeData = $request->only(['title', 'latitude', 'longitude', 'start_time', 'end_time']);
            $challengeData['team_id'] = $team->id;
            $challengeData['user_id'] = $apiUser->id;
            $challengeData['category'] = $category; // Set category from team
            $challenge = Challenge::create($challengeData);

            if($challenge->category === 'football') {
                $team->challengesParticipatedIn()->attach($challenge->id);
            } elseif($challenge->category === 'running') {
                $apiUser->challenges()->attach($challenge->id);
            }

            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
                $request->file('image')->move(public_path('storage/images/challenges'), $imageName);
                $challenge->image = 'storage/images/challenges/' . $imageName;
                $challenge->save();
            }

            if ($category === 'football') {
                if ($request->has('opponent_id')) {
                    ChallengeInvitation::create([
                        'challenge_id' => $challenge->id,
                        'model_type' => Team::class,
                        'model_id' => $request->input('opponent_id'),
                        'status' => 'pending',
                    ]);
                } else {
                    $teams = Team::where('id', '!=', $request->input('team_id'))->where('category', 'football')->get();
                    foreach ($teams as $team) {
                        ChallengeInvitation::create([
                            'challenge_id' => $challenge->id,
                            'model_type' => Team::class,
                            'model_id' => $team->id,
                            'status' => 'pending',
                        ]);
                    }
                }
            } elseif ($category === 'running') {
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
