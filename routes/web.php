<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HealthPlacesController;
use App\Http\Controllers\PublicChallengeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

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
    return view('layouts.dashboard');})->middleware(['auth', 'verified'])->name('dashboard');

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

Route::get('/createCategory',   [CategoryController::class, 'index'])->middleware('auth')->name('createCategory');
Route::post('/createCategory',  [CategoryController::class, 'store'])->middleware('auth')->name('createCategory');
Route::get('/editCategory/{id}',  [CategoryController::class, 'edit'])->name('editCategory');

Route::post('/updateCategory/{id}',  [CategoryController::class, 'update'])->name('updateCategory')->middleware('auth');
Route::get('/createPost',       [PostController::class, 'index'])->name('createPost')->middleware('auth');
Route::post('/createPost',      [PostController::class, 'store'])->name('createPost')->middleware('auth');
Route::get('/createChallenge',  [PublicChallengeController::class, 'index'])->name('createChallenge')->middleware('auth');
Route::post('/createChallenge', [PublicChallengeController::class, 'store'])->name('createChallenge')->middleware('auth');

Route::get('/createHealthPlace',  [HealthPlacesController::class, 'index'])->name('createHealthPlace')->middleware('auth');
Route::post('/createHealthPlace', [HealthPlacesController::class, 'store'])->name('createHealthPlace')->middleware('auth');
Route::get('/readHealthyPlaces',  [HealthPlacesController::class, 'create'])->name('readHealthyPlaces')->middleware('auth');
Route::delete('/deleteHealthPlace/{id}',[ HealthPlacesController::class,'destroy'])->name('deleteHealthPlace')->middleware('auth');
Route::get('/readPost',  [PostController::class, 'create'])->name('readPost')->middleware('auth');
Route::delete('/deletePost/{id}',[ PostController::class,'destroy'])->name('deletePost')->middleware('auth');

require __DIR__.'/auth.php';
