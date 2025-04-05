<?php
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationCountUpdated implements ShouldBroadcast
{
    public $unreadCount;

    public function __construct($unreadCount)
    {
        $this->unreadCount = $unreadCount;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . auth()->id());
    }

    public function broadcastAs()
    {
        return 'NotificationCountUpdated';
    }
}
