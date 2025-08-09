<?php

use Illuminate\Http\Request;
use App\Models\ChallengeResult;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ApiUserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatPeerController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\TimeUserController;
use App\Http\Controllers\GhallengesController;
use App\Http\Controllers\footballcylicontroller;
use App\Http\Controllers\HealthPlacesController;
use App\Http\Controllers\footballmatchController;
use App\Http\Controllers\ChallengeResultController;
use App\Http\Controllers\Mobile\ChallengesController;
use App\Http\Controllers\Mobile\InvitationsController;
use App\Http\Controllers\Mobile\TeamsController;
use App\Http\Controllers\PublicChallengeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});
Route::post('/register',        [ApiUserController::class,'store'])->middleware('api');
Route::post('/login',           [ApiUserController::class,'login'])->middleware('api');
Route::post('/updateUser/{id}', [ApiUserController::class,'update']);
Route::get('/getUser', [ApiUserController::class,'getAllUsers']);
Route::get('/search'           ,[ApiUserController::class,'search']);
Route::get('/AllUsers'           ,[ApiUserController::class,'show']);
Route::post('/refreshToken'           ,[ApiUserController::class,'refreshToken']);
/***CHAT_PEERS */
Route::post('/createChatPreer', [ChatPeerController::class,'store']);
/*Categories*/
Route::get('/getCategories' ,   [CategoryController::class , 'AllCategories']);

Route::get('/getPosts' ,        [PostController::class , 'AllPosts']);

Route::get('/createTeam',       [TeamController::class, 'index'])->name('getTeams');
// /createTeam API TeamController store method replaced with Mobile/TeamsController store method
// Route::post('/createTeam',      [TeamController::class, 'store'])->name('createTeam');
Route::post('/updateTeam/{id}', [TeamController::class, 'update'])->name('updateTeam');
Route::get('/myTeams',          [TeamController::class, 'myTeams'])->name('myTeams');
Route::get('/allTeams',          [TeamController::class, 'index'])->name('allTeams');
Route::get('/teamUsers/{id}',          [TeamController::class, 'teamUsers'])->name('teamUsers');

Route::post('/invitation/{id}', [TeamController::class, 'invitation'])->name('invitation');;
Route::get('/viewTeam/{id}',    [TeamController::class,  'viewTeam'] );

Route::get('/createChallengeTeam',                  [GhallengesController::class, 'index']);
Route::post('/createChallengeTeam/{teamID}',        [GhallengesController::class, 'store']);
Route::post('/updatePublicChallenge/{id}',          [PublicChallengeController::class,'update']);
Route::post('/ChallengeResult/{challengeID}/{team}',[ChallengeResultController::class,'store']);
Route::get('/viewResultChallenge/{challenge}',      [ChallengeResultController::class,'show']);
Route::get('/viewResultRunning/{challenge}',        [ChallengeResultController::class,'showRunning']);
Route::get('/viewResultfootball/{challenge}',       [ChallengeResultController::class,'showFootball']);
Route::get('/challengeDetails/{id}',                [GhallengesController::class,'viewChallenge']);



Route::get('/challengeData',                        [GhallengesController::class, 'show']);
Route::get('/viewChallenge/{id}',                   [GhallengesController::class, 'viewChallenge']);
Route::get('/challenges',                           [GhallengesController::class, 'challenges']);
Route::post('/allHealthyPlaces',                    [HealthPlacesController::class, 'getAll']);

Route::post('/newContact',                          [ContactsController::class, 'store']);
Route::get('/ViewContact',                          [ContactsController::class, 'show']);
Route::post('/addContacts',                         [ApiUserController::class,'contactTest'])->middleware('api');
// /leaveTeam API ApiUserController destroy method replaced with Mobile/TeamsController leaveTeam method
// Route::post('/leaveTeam',                           [ApiUserController::class, 'destroy']);
Route::post('/startTime/{challengeID}/{team}',      [TimeUserController::class, 'store']);

Route::post('/cylic/{id}', [footballcylicontroller::class,'update'])->named('editCylic');
Route::post('/match/{id}', [footballmatchController::class,'store']);

// New API Routes for new updates
Route::get('/challenges/user/football', [ChallengesController::class, 'userFootballChallenges']);
Route::get('/challenges/user/running', [ChallengesController::class, 'userRunningChallenges']);
Route::get('/challenges/{challenge_id}/results', [ChallengesController::class, 'challengeResults']);
Route::get('/challenges/{interval_category}/{limit}', [GhallengesController::class, 'challengesBeforeOrAfter']);

Route::prefix('/teams')->controller(TeamsController::class)->group(function () {
    Route::post('/', 'store');
    Route::post('/leave', 'leaveTeam');
    Route::get('/{team_id}/members', 'membersList');
    Route::get('/{team_id}/challenges', 'challengesParticipatedInList');
});

Route::post('/createTeam', [TeamsController::class, 'store'])->name('createTeam'); // Bind createTeam API to new Controller
Route::post('/leaveTeam', [TeamsController::class, 'leaveTeam']); // Bind leaveTeam API to new Controller

Route::prefix('/invitations')->controller(InvitationsController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::post('/{invitation_id}/status', 'resppondToInvitation');
});
