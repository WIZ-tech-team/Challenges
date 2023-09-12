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

Route::get('/createCategory',   [CategoryController::class, 'index'])->name('createCategory');
Route::post('/createCategory',  [CategoryController::class, 'store'])->name('createCategory');
Route::get('/editCategory/{id}',  [CategoryController::class, 'edit'])->name('editCategory');

Route::post('/updateCategory/{id}',  [CategoryController::class, 'update'])->name('updateCategory');
Route::get('/createPost',       [PostController::class, 'index'])->name('createPost');
Route::post('/createPost',      [PostController::class, 'store'])->name('createPost');
Route::get('/createChallenge',  [PublicChallengeController::class, 'index'])->name('createChallenge');
Route::post('/createChallenge', [PublicChallengeController::class, 'store'])->name('createChallenge');

Route::get('/createHealthPlace',  [HealthPlacesController::class, 'index'])->name('createHealthPlace');
Route::post('/createHealthPlace', [HealthPlacesController::class, 'store'])->name('createHealthPlace');
Route::get('/readHealthyPlaces',  [HealthPlacesController::class, 'create'])->name('readHealthyPlaces');
Route::delete('/deleteHealthPlace/{id}',[ HealthPlacesController::class,'destroy'])->name('deleteHealthPlace');
Route::get('/readPost',  [PostController::class, 'create'])->name('readPosxt');
Route::delete('/deletePost/{id}',[ PostController::class,'destroy'])->name('deletePost');

require __DIR__.'/auth.php';
