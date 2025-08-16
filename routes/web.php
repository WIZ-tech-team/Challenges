<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\footballcylicontroller;
use App\Http\Controllers\HealthPlacesController;
use App\Http\Controllers\PublicChallengeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\AwardsController;
use App\Http\Controllers\Web\ChallengesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('home');})->middleware(['auth', 'verified'])->name('dashboard');

// Route::get('/sign-up', function () {
//     return view('layouts.sign-up');
// });
// Route::get('/sign-in', function () {
//     return view('layouts.sign-in');
// });

Route::get('/logout',[AuthenticatedSessionController::class , 'destroy'])->middleware(['auth', 'verified'])->name('dashboard');;
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile',     [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',   [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',  [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/readUser', function () {
    return view('readUser');
});
Route::get('/createCategory',   [CategoryController::class, 'index'])->middleware('auth')->name('createCategory');
Route::post('/createCategory',  [CategoryController::class, 'store'])->middleware('auth')->name('createCategory');
Route::get('/editCategory/{id}',[CategoryController::class, 'edit'])->name('editCategory');

Route::post('/updateCategory/{id}',  [CategoryController::class, 'update'])->name('updateCategory')->middleware('auth');
Route::get('/readCategory',          [CategoryController::class, 'create'])->name('readCat')->middleware('auth');
Route::delete('/deleteCategory/{id}',[ CategoryController::class,'destroy'])->name('deleteCategory')->middleware('auth');
Route::get('/categories/search',     [CategoryController::class,'search'])->name('searchCat');

Route::get('/createPost',              [PostController::class, 'index'])->name('createPost')->middleware('auth');
Route::post('/createPost',             [PostController::class, 'store'])->name('createPost')->middleware('auth');
Route::get('/createChallenge',         [PublicChallengeController::class, 'index'])->name('createChallenge')->middleware('auth');
Route::post('/createChallenge',        [PublicChallengeController::class, 'store'])->name('createChallenge')->middleware('auth');
Route::get('/readChallenges',          [PublicChallengeController::class, 'create'])->name('readChallenges')->middleware('auth');
Route::get('/readRunning',             [PublicChallengeController::class, 'readRunning'])->name('readRunning')->middleware('auth');
Route::get('/challenges/search',       [PublicChallengeController::class,'search'])->name('searchChallenges');
Route::get('/challenges/searchRunning',[PublicChallengeController::class,'searchRunning'])->name('searchRunning');

Route::delete('/deleteChallenge/{id}',  [PublicChallengeController::class,'destroy'])->name('deleteChallenge')->middleware('auth');

Route::get('/createHealthPlace',        [HealthPlacesController::class, 'index'])->name('createHealthPlace')->middleware('auth');
Route::post('/createHealthPlace',       [HealthPlacesController::class, 'store'])->name('createHealthPlace')->middleware('auth');
Route::get('/readHealthyPlaces',        [HealthPlacesController::class, 'create'])->name('readHealthyPlaces')->middleware('auth');
Route::delete('/deleteHealthPlace/{id}',[HealthPlacesController::class,'destroy'])->name('deleteHealthPlace')->middleware('auth');
Route::get('/readPost',                 [PostController::class, 'create'])->name('readPost')->middleware('auth');
Route::delete('/deletePost/{id}',       [PostController::class,'destroy'])->name('deletePost')->middleware('auth');
Route::get('/health-places/search',     [HealthPlacesController::class,'search'])->name('searchHealthPlaces');
Route::get('/posts/search',             [PostController::class,'search'])->name('searchPosts');
Route::post('/cylic/{id}',              [footballcylicontroller::class,'update'])->name('editCylic');
Route::post('/cylic/{id}',              [footballcylicontroller::class,'update'])->name('editCylic');

Route::get('/viewCylic',                [PublicChallengeController::class,'showCylic']);
Route::get('/viewCylicChallenge/{id}',  [PublicChallengeController::class,'viewCylicChallenge'])->name('viewCylicChallenge');
/*Route::resource('/cities', CityController::class)->names('cities');*/
Route::post('/load-teams',      [PublicChallengeController::class,'loadTeams'])->name('loadTeams');

Route::get('/cities', [CityController::class,'index'])->name('cities');
Route::get('/cities/create', [CityController::class,'create'])->name('create_city');
Route::post('/cities', [CityController::class,'store'])->name('store_city');
Route::get('/cities/{id}/edit', [CityController::class,'edit'])->name('edit_city');
Route::put('/cities/{id}', [CityController::class,'update'])->name('update_city');
Route::delete('/cities/{id}', [CityController::class,'destroy'])->name('delete_city');
//Route::get('/cities/{id}', [CityController::class,'show'])->name('view_city');

Route::prefix('dashboard')->group(function () {
    Route::prefix('challenges')->controller(ChallengesController::class)->group(function () {
        Route::get('/', 'index')->name('challenges.index');
        Route::get('/create', 'create')->name('challenges.create');
        Route::delete('/{challenge_id}', 'destroy')->name('challenges.destroy');
    });

    Route::prefix('awards')->controller(AwardsController::class)->group(function () {
        Route::get('/', 'index')->name('awards.index');
        Route::get('/create', 'create')->name('awards.create');
        Route::delete('/{challenge_id}', 'destroy')->name('awards.destroy');
    });
    
});

require __DIR__.'/auth.php';
