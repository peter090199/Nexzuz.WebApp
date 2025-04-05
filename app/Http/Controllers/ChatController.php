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

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'created_at' => now(),
            'is_read' => false
        ]);

        // ✅ Broadcast the message in real-time
        event(new MessageSent($message));
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully!',
            'data' => $message
        ], 200);
    }


  // ✅ Fetch chat messages
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

    public function fetchMessagesx($receiverId)
    {
        $messages = DB::table('messages')
            ->where(function ($query) use ($receiverId) {
                $query->where('sender_id', Auth::id())
                      ->where('receiver_id', $receiverId);
            })
            ->orWhere(function ($query) use ($receiverId) {
                $query->where('sender_id', $receiverId)
                      ->where('receiver_id', Auth::id());
            })
            ->orderBy('created_at', 'asc')
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

        // Trigger the event
        broadcast(new NotificationCountUpdated($userId, $unreadCount));
        
        return response()->json([
            'unreadCount' => $unreadCount
        ]);
    }

    public function getNotifications()
    {
        $userId = Auth::id();
    
        $notifications = Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->with('sender')
            ->orderByDesc('created_at')
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
}
