<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\PostController;
use App\Http\Controllers\MessageController;
use App\Events\MessageSent;

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

Broadcast::routes(['middleware' => ['auth:api']]);

// Sample pusher notify realtime
Route::get('/pusher', function () {
    return view('pusher');
});

Route::get('/pusher2', function () {
    return view('pusher2');
});

Route::get('/pusher3', function () {
    return view('pusher3');
});

Route::get('/user/post', [MessageController::class, 'showForm']);
Route::post('/user/postSave', [MessageController::class, 'save'])->name('post.save');

Route::get('/postuser', function () {
    return view('testuploads');
});

Route::resource('testpost', PostController::class)->names('testpost');

Route::get('/test-broadcast', function () {
    $message = (object) [
        'id' => 146,
        'sender_id' => 92,
        'receiver_id' => 91,
        'message' => 'receive',
        'created_at' => now(),
    ];

    event(new MessageSent($message));

    return response()->json([
        'success' => true,
        'broadcasted_data' => $message,
    ]);
});

// -----------------------------------------------------
// SERVE STORAGE FILES DIRECTLY (Windows symlink workaround)
// -----------------------------------------------------
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'pdf'  => 'application/pdf',
    ];

    $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response()->file($fullPath, [
        'Content-Type' => $contentType,
    ]);
})->where('path', '.*');


Route::get('/raw-image-test', function () {
    $path = storage_path('app/public/uploads/703/JobPosting/88abfdf8-f75d-4b5d-8e90-250481c570ca/1785628090.jpg');
    return response(file_get_contents($path))->header('Content-Type', 'image/jpeg');
});