<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;
use App\Models\Message;


class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'receiver_id' => 'required|integer'
        ]);
        
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);
    
        // Now $message is an Eloquent model, so it's compatible with the MessageSent event
        broadcast(new MessageSent($message))->toOthers();
    
        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully!',
            'data' => $message
        ], 200);
    }


    public function fetchMessages($receiverId)
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


    
}
