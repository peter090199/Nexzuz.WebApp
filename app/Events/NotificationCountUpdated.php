<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\BroadcastEvent;

class NotificationCountUpdated implements Broadcasting
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $unreadCount;

    public function __construct($userId, $unreadCount)
    {
        $this->userId = $userId;
        $this->unreadCount = $unreadCount;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }
    public function broadcastWith()
    {
        return [
            'unreadCount' => $this->unreadCount,  // Data to be broadcasted
        ];
    }
    public function broadcastAs()
    {
        return 'NotificationCountUpdated';
    }
}
