<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\Auth\ProfileController;
use  App\Http\Controllers\Auth\PostController;
use  App\Http\Controllers\MessageController;

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
    return view('welcome');
});


//sample pusher notifiy realtime
Route::get('/pusher', function () {
    return view('pusher');
});

Route::get('/pusher2', function () {
    return view('pusher2');
});

Route::get('/pusher3', function () {
    return view('pusher3');
});

Route::get('/user/post',[MessageController::class,'showForm']);
Route::post('/user/postSave',[MessageController::class,'save'])->name('post.save');

// Route::resource('profiles',ProfileController::class)->names('profiles');
Route::resource('testpost',PostController::class)->names('testpost');