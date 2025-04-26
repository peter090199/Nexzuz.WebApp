<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;
use App\Models\Message;
use App\Events\NotificationCountUpdated;

class ChatController extends Controller
{
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'receiver_id' => 'required|integer'
        ]);
        $codeuser = Auth::user()->code;
        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'created_at' => now(),
            'code' => $codeuser,
            'is_read' => false
        ]);

          // Get unread count for the receiver
            $unreadCount = Message::where('receiver_id', $request->receiver_id)
            ->where('is_read', false)
            ->count();
        
            broadcast(new NotificationCountUpdated($request->receiver_id, $unreadCount))->toOthers();
  
       // broadcast(new MessageSent($message));
        
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully!',
            'data' => $message
        ], 200);
    }


  // ✅ get by userId chat messages
  public function fetchMessages($receiverId) {
        $userId = Auth::id();
        $messages = Message::where(function($query) use ($userId, $receiverId) {
                $query->where('sender_id', $userId)->where('receiver_id', $receiverId);
            })->orWhere(function($query) use ($userId, $receiverId) {
                $query->where('sender_id', $receiverId)->where('receiver_id', $userId);
            })->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

  

    //DISPLAY CHAT ONLINE MESSAGES
    public function getActiveUsers()
    {
        $users = DB::table('users')
            ->leftJoin('userprofiles', 'userprofiles.code', '=', 'users.code')
            ->select('users.id', 'users.code', 'users.status', 'users.fullname', 'userprofiles.photo_pic','users.is_online')
            ->where('users.status', 'A')
            ->get();

        return response()->json($users);
    }

    
    public function testBroadcast()
    {
        event(new NotificationCountUpdated(auth()->id(), 5));
        return response()->json(['message' => 'Broadcast sent']);
    }
    
    public function updateNotificationCount()
    {
        $userId = Auth::id();
    
        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();  
    
        // ✅ Pass both userId and unreadCount to the event
        broadcast(new NotificationCountUpdated($userId, $unreadCount));
    
        return response()->json([
            'unreadCount' => $unreadCount
        ]);
    }


    public function updateNotificationCountxx()
    {
        $userId = Auth::id();
        $unreadCount = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();  

        // Assuming the latest message for this user (adjust as necessary)
            // $message = Message::where('receiver_id', $userId)
            // ->latest()
            // ->first();

         broadcast(new NotificationCountUpdated($unreadCount));
        return response()->json([
            'unreadCount' => $unreadCount
        ]);
    }

    public function getNotificationsIsRead()
    {
        $userId = Auth::id();
            $notifications = DB::table('messages')
            ->join('users', 'messages.sender_id', '=', 'users.id')
            ->join('userprofiles', 'users.code', '=', 'userprofiles.code')
            ->where('messages.receiver_id', $userId)
            ->where('messages.is_read', false)
            ->orderByDesc('messages.created_at')
            ->select('messages.*', 'userprofiles.photo_pic','users.fullname')
            ->get();
    
        return response()->json([
            'notifications' => $notifications
        ]);
    }

    public function getNotificationsIsUnReadBySenderId()
    {
        $userId = Auth::id();

        $subQuery = DB::table('messages')
            ->selectRaw('MAX(id) as id')
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->groupBy('sender_id');

        $notifications = DB::table('messages')
            ->join('users', 'messages.sender_id', '=', 'users.id')
            ->join('userprofiles', 'users.code', '=', 'userprofiles.code')
            ->whereIn('messages.id', $subQuery)
            ->select('messages.*', 'userprofiles.photo_pic', 'users.fullname')
            ->orderByDesc('messages.created_at')
            ->get();

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    public function getMessagesByUserId()
    {
        $userId = Auth::id();
        // Subquery to get the latest unread message per sender
        $latestMessages = DB::table('messages as m1')
            ->select('m1.*')
            ->where('m1.receiver_id', $userId)
            ->where('m1.is_read', false)
            ->whereRaw('m1.id = (
                SELECT m2.id FROM messages m2
                WHERE m2.sender_id = m1.sender_id 
                  AND m2.receiver_id = ? 
                  AND m2.is_read = false
                ORDER BY m2.created_at DESC
                LIMIT 1
            )', [$userId]);
    
        // Join with users and profiles
        $notifications = DB::table(DB::raw("({$latestMessages->toSql()}) as messages"))
            ->mergeBindings($latestMessages)
            ->join('users', 'messages.sender_id', '=', 'users.id')
            ->join('userprofiles', 'users.code', '=', 'userprofiles.code')
            ->select('messages.*', 'userprofiles.photo_pic', 'users.fullname')
            ->orderByDesc('messages.created_at')
            ->get();
    
        return response()->json([
            'notifications' => $notifications
        ]);

    }
    

    public function getNotificationsIsUnRead()
    {
        $userId = Auth::id();
            $notifications = DB::table('messages')
            ->join('users', 'messages.sender_id', '=', 'users.id')
            ->join('userprofiles', 'users.code', '=', 'userprofiles.code')
            ->where('messages.receiver_id', $userId)
            ->where('messages.is_read', true)
            ->orderByDesc('messages.created_at')
            ->select('messages.*', 'userprofiles.photo_pic','users.fullname')
            ->groupBy('receiver_id')
            ->get();
    
        return response()->json([
            'notifications' => $notifications
        ]);
    }


      // ✅ Mark messages as read
      public function markAsRead(Request $request) {
        Message::where('id', $request->id)
            ->where('receiver_id', Auth::id())
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'All messages marked as read']);
    }
}
