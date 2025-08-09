<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ApiUser;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if($challenge->category == 'football') {
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
}
