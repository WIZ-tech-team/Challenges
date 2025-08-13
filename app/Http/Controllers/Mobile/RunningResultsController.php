<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ApiUser;
use App\Models\Challenge;
use App\Models\RunningResult;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RunningResultsController extends Controller
{
    public function store(Request $request, $challenge_id)
    {
        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'user_id' => 'required|exists:api_users,id',
            'steps' => 'required|integer',
            'distance' => 'required|numeric',
            'duration' => 'required|date_format:H:i:s',
            'rank' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
            'award_id' => 'nullable|exists:awards,id'
        ]);

        $challenge = Challenge::findOrFail($challenge_id);
        if ($challenge->category !== 'running') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Challenge is not running.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $existingResult = RunningResult::where('challenge_id', $challenge_id)
            ->where('user_id', $request->input('user_id'))
            ->exists();
        if ($existingResult) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Result already exists for this challenge participant.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $team = Team::find($request->input('team_id'));
        $user = ApiUser::find($request->input('user_id'));
        if (!$team->members()->where('api_users.id', $user->id)->exists()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User is not a member of the team.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$challenge->users()->where('api_users.id', $user->id)->exists()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User is not a participant in the challenge.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = RunningResult::create([
            'challenge_id' => $challenge_id,
            'team_id' => $request->input('team_id'),
            'user_id' => $request->input('user_id'),
            'steps' => $request->input('steps'),
            'distance' => $request->input('distance'),
            'duration' => $request->input('duration'),
            'rank' => $request->input('rank'),
            'points' => $request->input('points'),
            'award_id' => $request->input('award_id'),
        ]);

        $user->points = ($user->points ?? 0) + ($request->input('points') ?? 0);
        $user->save();

        return response()->json([
            'status' => 'success',
            'data' => $result
        ], Response::HTTP_OK);
    }
}
