<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class NotificationCountUpdated implements ShouldBroadcast
{
    public $userId;
    public $unreadCount;

    public function __construct($userId, $unreadCount)
    {
        Log::info("Broadcasting count to user {$userId}: {$unreadCount}");
        $this->userId = $userId;
        $this->unreadCount = $unreadCount;
    }

    public function broadcastOn()//CHANNEL
    {
        return new Channel('notification.count.' . $this->userId);
    }

    public function broadcastWith()
    {
        return ['unreadCount' => $this->unreadCount];
    }

    public function broadcastAs()
    {
        return 'NotificationCountUpdated';
    }
}
