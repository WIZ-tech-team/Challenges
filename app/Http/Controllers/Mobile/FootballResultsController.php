<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\FootballResult;
use App\Models\Team;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FootballResultsController extends Controller
{
    public function store(Request $request, $challenge_id)
    {
        $request->validate([
            'team_1_id' => 'required|exists:teams,id',
            'team_1_score' => 'required|integer|min:0',
            'team_1_points' => 'required|integer|min:0',
            'team_1_award_id' => 'nullable|exists:awards,id',
            'team_2_id' => 'required|exists:teams,id|different:team_1_id',
            'team_2_score' => 'required|integer|min:0',
            'team_2_points' => 'required|integer|min:0',
            'team_2_award_id' => 'nullable|exists:awards,id'
        ]);

        $challenge = Challenge::findOrFail($challenge_id);
        if ($challenge->category !== 'football') {
            return response()->json([
                'status' => 'failed',
                'message' => 'Challenge is not football.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $participantTeamIds = $challenge->participantTeams()->pluck('team_id')->toArray();

        if (count($participantTeamIds) != 2) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Challenge must have exactly two participant teams.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!in_array($request->input('team_1_id'), $participantTeamIds) || !in_array($request->input('team_2_id'), $participantTeamIds)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Teams must be participants of the challenge.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $existingResult = FootballResult::where('challenge_id', $challenge_id)->exists();
        if ($existingResult) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Result already exists for this challenge.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = FootballResult::create([
            'challenge_id' => $challenge_id,
            'team_1_id' => $request->input('team_1_id'),
            'team_1_score' => $request->input('team_1_score'),
            'team_1_points' => $request->input('team_1_points'),
            'team_1_award_id' => $request->input('team_1_award_id'),
            'team_2_id' => $request->input('team_2_id'),
            'team_2_score' => $request->input('team_2_score'),
            'team_2_points' => $request->input('team_2_points'),
            'team_2_award_id' => $request->input('team_2_award_id')
        ]);

        $team1 = Team::find($request->input('team_1_id'));
        foreach ($team1->members as $member) {
            $member->points = ($member->points ?? 0) + ($request->input('team_1_points') ?? 0);
            $member->save();
        }

        $team2 = Team::find($request->input('team_2_id'));
        foreach ($team2->members as $member) {
            $member->points = ($member->points ?? 0) + ($request->input('team_2_points') ?? 0);
            $member->save();
        }

        return response()->json([
            'status' => 'success',
            'data' => $result
        ], Response::HTTP_OK);
    }
}
