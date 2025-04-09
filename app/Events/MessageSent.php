<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


use App\Models\Message;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
   // public $unreadCount;

    public function __construct($message,$unreadCount)
    {
        $this->message = $message;
      //  $this->unreadCount = $unreadCount;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . $this->message['receiver_id']);
    }
    public function broadcastAs()
    {
        return 'message.sent';
    }
 
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at,
            'unreadCount' => $this->message->unreadCount,
        ];
    }
}
