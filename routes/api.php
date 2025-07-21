<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

use  App\Http\Controllers\Lookup\LookupController;

use  App\Http\Controllers\Auth\LoginController;
use  App\Http\Controllers\Auth\RegisterController;
use  App\Http\Controllers\Auth\ForgetpasswordController;
use  App\Http\Controllers\Auth\ProfileController;
use  App\Http\Controllers\Auth\ProfilepictureController;
use  App\Http\Controllers\Auth\PostController;
use  App\Http\Controllers\Postcomments\CommentController;

use App\Http\Controllers\Accessrolemenu\AccessrolemenuController;

use App\Http\Controllers\System\Menus\MenuController;
use App\Http\Controllers\System\Securityroles\SecurityroleController;

use App\Http\Controllers\System\Roles\RoleController;
use App\Http\Controllers\SearchAccount\UserController;
use App\Http\Controllers\Select2\SelectController;
use App\Http\Controllers\ChatController;

use App\Http\Controllers\Follow\ClientsBAL;
use App\Http\Controllers\SearchAccount\SearchHistoryBAL;

use App\Events\MessageSent; 
use App\Events\Message;
use App\Events\NotificationCountUpdated;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/  

// PUBLIC
Route::post('login',[LoginController::class,'login'])->name('login');

Route::post('resetpassword',[ForgetpasswordController::class,'resetpassword'])->name('resetpassword');

Route::post('forgetpassword',[ForgetpasswordController::class,'forgetpassword'])->name('forgetpassword');

Route::post('register',[RegisterController::class,'register'])->name('register');

Route::post('accountactivation',[RegisterController::class,'accountactivation'])->name('accountactivation');

Route::post('send-message', function (Request $request) {
    $message = $request->input('message');

    event(new MessageSent($message)); // ✅ Corrected event class name

    return response()->json(['success' => true, 'message' => $message]);
});

Route::middleware('auth:api')->post('/profile/broadcasting/auth', function () {
    return Broadcast::auth(request());
});

Route::middleware(['auth:sanctum','checkstatus'])->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user(); 
    });
    
    //logout
    Route::post('logout',[LoginController::class,'logout'])->name('logout');


    // PROFILE resource
    Route::Resource('profile',ProfileController::class)->names('profile');
    Route::get('user/profile',[ProfileController::class,'userAuth'])->name('user/profile');
    Route::resource('profile_pic',ProfilepictureController::class)->names('profile_pic');

    // Accessrolemenu
    // User access to the menu depends on their role. GET 
    Route::Resource('accessmenu',AccessrolemenuController::class)->names('accessmenu');

    // Menus
    // menu GET , STORE 
    Route::Resource('menu',MenuController::class)->names('menu');

    // Security roles
    // security GET , STORE 
    Route::Resource('security',SecurityroleController::class)->names('security');

    //Role
    // role GET,STORE,UPDATE,SHOW
    Route::Resource('role',RoleController::class)->names('role');

    // SELECT2 ALL REQUEST
    Route::post('rolecode',[SelectController::class,'rolecode'])->name('rolecode');
    
    
    // lookup information
    Route::get('userlists',[LookupController::class,'userlists'])->name('userlists');

  
    //search fullname
    Route::get('searchUsers', [UserController::class, 'searchUsers']);

   //get Onlineusers
   Route::get('getIsOnline', [LoginController::class, 'getIsOnline']);
 
   //chat meesages realtime
   Route::post('send-message', [ChatController::class, 'sendMessage']);
   Route::post('messages/read', [ChatController::class, 'markAsRead']);
   Route::get('receivemessages/{receiverId}', [ChatController::class, 'fetchMessages']);
   Route::get('getActiveUsers', [ChatController::class, 'getActiveUsers']);
   Route::put('messagesIsread', [ChatController::class, 'markAsReadMessage']);

   Route::get('update_count', [ChatController::class, 'updateNotificationCount']);
   Route::get('getDataPost', [PostController::class, 'getDataPost']);

   Route::get('getNotificationsIsUnRead', [ChatController::class, 'getNotificationsIsUnRead']);
   Route::get('getNotificationsIsRead', [ChatController::class, 'getNotificationsIsRead']);
  
   Route::post('messages/mark_allAsread', [ChatController::class, 'markAllAsRead']);
   Route::get('messages_receive/{receiverId}', [ChatController::class, 'messages_receive']);
   Route::get('getMessagesAll', [ChatController::class, 'getMessagesAll']);
   
   //Post 
   Route::resource('post',PostController::class)->names('post');
   Route::post('deleteindidualpost/{id}', [PostController::class, 'deleteIndividualPost']);
   //Comment
   Route::resource('comment',CommentController::class)->names('comment');
   Route::post('commentreply', [CommentController::class, 'commentreply']);
   //Reactions  
   Route::resource('reaction',App\Http\Controllers\Postreaction\PostreactionController::class)->names('reaction');
   //Follow  App\Http\Controllers\Follow
   Route::resource('follow',App\Http\Controllers\Follow\FollowController::class)->names('follow');

    // Route::post('/post-attachment/{id}', [PostController::class, 'deleteIndividualPost']);


    //List clients base on rrofile
    Route::get('getListClients', [ClientsBAL::class, 'getListClients']);
    Route::get('getFollowStatus/{code}', [ClientsBAL::class, 'getFollowStatus']);
    Route::get('getPendingFollowStatus/{code}', [ClientsBAL::class, 'getPendingFollowStatus']);

    //List clients base on PENDING
    Route::get('getPendingFollowRequests', [ClientsBAL::class, 'getPendingFollowRequests']);
    Route::get('getfollowingPending', [ClientsBAL::class, 'getfollowingPending']);
    Route::put('acceptFollowRequest/{followerCode}', [ClientsBAL::class, 'acceptFollowRequest']);
    //unfollow
    Route::delete('unfollow/{id}', [ClientsBAL::class, 'unfollow']);
    // Suggested users based on profession or industry of followed people
     Route::get('getPeopleyoumayknow', [ClientsBAL::class, 'getPeopleyoumayknow']);

     Route::post('saveSearchHistory', [SearchHistoryBAL::class, 'saveSearchHistory']);
     Route::get('getSearchHistory', [SearchHistoryBAL::class, 'getSearchHistory']);
     Route::get('getPeopleRecentActivity', [ClientsBAL::class, 'getPeopleRecentActivity']);

});
