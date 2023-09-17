<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\ApiUser;
use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendEndTimeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $timezone = 'Asia/Gaza';
       
        $currentTime = Carbon::now($timezone);
     
        

        $challenges = Challenge::where('end_time', '<=', $currentTime)
        ->where('category_id', 2)
        ->where('status','started')
        ->get();
        
        foreach ($challenges as $challenge) {
            $challengeId = $challenge->id;

            // Use a join query to retrieve users with the same team_id as the challenge
            $teamUsers = DB::table('challenges')
                ->join('api_users', 'challenges.team_id', '=', 'api_users.team_id')
                ->where('challenges.id', $challengeId)
                ->select('api_users.*')
                ->get();
                $challenge->status = 'ended';
                $challenge->save();
            foreach ($teamUsers as $user) {
                
                $addNotificationData = [
                    'title' => 'Challenge Reminder',
                    'body' => 'Your team\'s challenge "' . $challenge->title . '" has ended.',
                    'click_action' => 'OPEN_CHAT',
                    'id' => $challenge->id,
                ];
        
                $addNotificationPayload = [
                    'to' => $user->fcm_token,
                    'notification' => $addNotificationData,
                ];
        
            }}
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
