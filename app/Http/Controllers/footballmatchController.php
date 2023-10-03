<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FootballMatch;
use Illuminate\Support\Facades\DB;

class footballmatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$id)
    {
        $a = DB::table('footballcylic_team')->where('cylic_id',$id)->get();
        $teamIds = $a->pluck('team_id')->toArray();

       shuffle($teamIds);

$matchPairings = [];

if (!empty($teamIds)) {
    $matchNum = 1; // Initialize match number

    for ($i = 0; $i < count($teamIds) - 1; $i += 2) {
        $team1 = $teamIds[$i];
        $team2 = $teamIds[$i + 1];

        $matchPairings[] = [
            'team1_id' => $team1,
            'team2_id' => $team2,
            'match_num' => $matchNum, // Assign the same matchNum to all pairings
        ];
    }

    if (count($teamIds) % 2 === 1) {
        $unpairedTeam = $teamIds[count($teamIds) - 1];
        $matchPairings[] = [
            'team1_id' => $unpairedTeam,
            'team2_id' => null,
            'match_num' => $matchNum, // Assign the same matchNum to unpaired teams
        ];
    }

    foreach ($matchPairings as $pair) {
        $match = new FootballMatch();
        $match->cylic_id = $id;
        $match->team1_id = $pair['team1_id'];
        $match->team2_id = $pair['team2_id']; 
        $match->matchNum = $pair['match_num'];
        $match->save();
    }
}

return $matchPairings;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
