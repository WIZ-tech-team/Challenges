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
        
        $challengesToStart = Challenge::where('start_time', '<=', $currentTime)
        ->where('category_id',1)
        ->where('status', 'created')
        ->get();

    foreach ($challengesToStart as $challenge) {
        $challengeId = $challenge->id;

        $challenge->status = 'started';
        $challenge->save();
        $teamUsers = DB::table('challenges')
        ->join('api_users', 'challenges.team_id', '=', 'api_users.team_id')
        ->where('challenges.id', $challengeId)
        ->select('api_users.*')
        ->get();
        foreach ($teamUsers as $user) {
            $addNotificationData = [
                'title' => 'Challenge Reminder',
                'body' => 'Football challenge "' . $challenge->title . '" has started.',
                'click_action' => 'OPEN_CHAT',
                'id' => $challenge->id,
            ];
            $addNotificationPayload = [
                'to' => $user->fcm_token,
                'notification' => $addNotificationData,];
                FirebaseService::send($user->fcm_token, $addNotificationPayload);
    }}
/**************************************************************** */

        $challenges = Challenge::where('end_time', '<=', $currentTime)
        ->where('status','started')
        ->get();
        
        foreach ($challenges as $challenge) {
                $challengeId = $challenge->id;
                $challenge->status = 'ended';
                $challenge->save();
                if ($challenge->category_id == 2 && $challenge->type =='private'){
                    $teamUsers = DB::table('challenges')
                    ->join('api_users', 'challenges.team_id', '=', 'api_users.team_id')
                    ->where('challenges.id', $challengeId)
                    ->select('api_users.*')
                    ->get();
                    foreach ($teamUsers as $user) {
                        $challengeResults = DB::table('challenge_results')
                        ->where('challenge_id', $challengeId)
                        ->orderByDesc('result_data')
                        ->get();
                        $addNotificationData = [
                            'title' => 'Challenge Reminder',
                            'body' => 'Running challenge "' . $challenge->title . '" has ended.',
                            'click_action' => 'OPEN_CHAT',
                            'id' => $challenge->id,
                        ];
                        $addNotificationPayload = [
                            'to' => $user->fcm_token,
                            'notification' => $addNotificationData,];
                        }
                        FirebaseService::send($user->fcm_token, $addNotificationPayload);

                     $points = [10, 9, 8];

                  foreach ($challengeResults as $index => $result) {
                         if ($index < 3) {
                          if ($result->user_id) {
                            $pointValue = $points[$index];
                             DB::table('api_users') 
                              ->where('id', $result->user_id)
                              ->increment('points', $pointValue);
                           }
             } else {
                break; 
            }}}elseif($challenge->category_id == 1)
            {  
                $TeamId = $challenge->team_id;
                $opponentTeamId = $challenge->opponent_id;

        $challengeResult = ChallengeResult::where('challenge_id', $challengeId)->first();
        $highestResult = max($challengeResult->result_data, $challengeResult->opponent_result);

         if ($highestResult === $challengeResult->result_data) {
                $winningTeamUsers = DB::table('api_users')
                ->where('team_id', $TeamId)
                ->get();
                 $losersUsers = DB::table('api_users')
                ->where('team_id', $opponentTeamId)
                ->get();
            } elseif($highestResult === $challengeResult->opponent_result) {
                   $winningTeamUsers = DB::table('api_users')
                  ->where('team_id', $opponentTeamId)
                  ->get();
                  $losersUsers = DB::table('api_users')
                  ->where('team_id', $TeamId)
                  ->get();
                }

                foreach ($winningTeamUsers as $user) {
                    DB::table('api_users')
                        ->where('id', $user->id)
                        ->increment('points', 20); 

                        $addNotificationData = [
                            'title' => 'Congratulations !',
                            'body' => 'You are the winner team in "' . $challenge->title . '" challenge.',
                            'click_action' => 'OPEN_CHAT',
                            'id' => $challenge->id,
                        ];
                        $addNotificationPayload = [
                            'to' => $user->fcm_token,
                            'notification' => $addNotificationData,];
                            FirebaseService::send($user->fcm_token, $addNotificationPayload);
                }

                foreach ($losersUsers as $user) {
                    DB::table('api_users')
                        ->where('id', $user->id)
                        ->increment('points', 10); 
             
                
                $addNotificationData = [
                    'title' => 'Hard luck',
                    'body' => 'You are the loser team in  "' . $challenge->title . '" challenge.',
                    'click_action' => 'OPEN_CHAT',
                    'id' => $challenge->id,
                ];
                $addNotificationPayload = [
                    'to' => $user->fcm_token,
                    'notification' => $addNotificationData,];
                    FirebaseService::send($user->fcm_token, $addNotificationPayload);
                  }}elseif($challenge->category_id == 2 && $challenge->type =='public'){
                    $teamUsers = DB::table('challenges_api_users')
                    ->join('api_users', 'challenges_api_users.users_id', '=', 'api_users.id')
                    ->where('challenges_api_users.challenge_id', $challengeId)
                    ->select('api_users.*')
                    ->get();

                    foreach ($teamUsers as $user) {
                        $challengeResults = DB::table('challenge_results')
                        ->where('challenge_id', $challengeId)
                        ->orderByDesc('result_data')
                        ->get();
                        $addNotificationData = [
                            'title' => 'Challenge Reminder',
                            'body' => 'Running challenge "' . $challenge->title . '" has ended.',
                            'click_action' => 'OPEN_CHAT',
                            'id' => $challenge->id,
                        ];
                        $addNotificationPayload = [
                            'to' => $user->fcm_token,
                            'notification' => $addNotificationData,];
                        }
                        FirebaseService::send($user->fcm_token, $addNotificationPayload);

                     $points = [10, 9, 8];

                  foreach ($challengeResults as $index => $result) {
                         if ($index < 3) {
                          if ($result->user_id) {
                         
                    $pointValue = $challenge->winners->points;
                    DB::table('api_users')
                        ->where('id', $result->user_id)
                        ->increment('points', $pointValue);
                    $points--; 
                           }
                    } else {
                      break; 
                          }
                     }
                  }
             }
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
