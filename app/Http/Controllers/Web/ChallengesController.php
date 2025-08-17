<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApiUser;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\ChallengeInvitation;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChallengesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $challenges = Challenge::all();
        return view('dashboard/challenges/index', ['challenges' => $challenges]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $teams = Team::all();
        return view('dashboard/challenges/create', compact('teams'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'title' => 'required|string|max:255',
            'category' => 'required|in:football,running',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'required|string|max:255',
            'participant_type' => 'required|in:invite,participate',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'image' => 'nullable|image|max:2048',
        ];

        // Football teams validation
        if ($request->category === 'football') {
            $rules['teams'] = [
                'required',
                'array',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->participant_type === 'invite' && count($value) < 2) {
                        $fail('For Invite, select at least 2 teams.');
                    }
                    if ($request->participant_type === 'participate' && count($value) !== 2) {
                        $fail('For Participate, select exactly 2 teams.');
                    }
                }
            ];
        }

        // Running teams validation
        if ($request->category === 'running') {
            $rules['teams_running'] = 'required|array|min:1';
            $rules['distance'] = 'required|numeric|min:0.01';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('challenges', 'public');
            }

            $challenge = new Challenge();
            $challenge->title = $validated['title'];
            $challenge->category = $validated['category'];
            $challenge->latitude = $validated['latitude'];
            $challenge->longitude = $validated['longitude'];
            $challenge->address = $validated['address'];
            $challenge->start_time = $validated['start_time'];
            $challenge->end_time = $validated['end_time'];
            $challenge->image = $imagePath;
            $challenge->save();

            if ($challenge->category === 'football') {
                if ($validated['participant_type'] === 'participate') {
                    $challenge->participantTeams()->attach($validated['teams']);
                } elseif ($validated['participant_type'] === 'invite') {
                    foreach ($validated['teams'] as $teamId) {
                        $team = Team::findOrFail($teamId);
                        ChallengeInvitation::create([
                            'challenge_id' => $challenge->id,
                            'model_type' => Team::class,
                            'model_id' => $team->id,
                            'status' => 'pending'
                        ]);
                    }
                }
            } elseif ($challenge->category === 'running') {
                // Get all teams memebers
                $allMembers = [];
                foreach ($validated['teams_running'] as $teamId) {
                    $team = Team::findOrFail($teamId);
                    $members = $team->members()->pluck('user_id')->toArray();
                    $allMembers = array_merge($allMembers, $members);
                }

                // Invite members or make them Participants
                if ($validated['participant_type'] === 'participate') {
                    $challenge->users()->attach($allMembers);
                } elseif ($validated['participant_type'] === 'invite') {
                    foreach ($allMembers as $member) {
                        ChallengeInvitation::create([
                            'challenge_id' => $challenge->id,
                            'model_type' => ApiUser::class,
                            'model_id' => $member,
                            'status' => 'pending'
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Challenge created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Failed to create challenge: ' . $e->getMessage());
        }
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
    public function destroy($challenge_id)
    {
        $challenge = Challenge::findOrFail($challenge_id);
        $challenge->delete();
        ChallengeInvitation::where('challenge_id', $challenge->id)->delete();

        return redirect()->route('challenges.index')->with('success', 'Challenge deleted successfully.');
    }
}
