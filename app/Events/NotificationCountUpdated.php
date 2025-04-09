<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCountUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $unreadCount;

    public function __construct($unreadCount)
    {
        $this->unreadCount = $unreadCount;
    }

    public function broadcastOn()
    {
        return new Channel('notification.count');
    }

    // Event name when broadcasting
    public function broadcastAs()
    {
        return 'notifications.count'; // You can keep the same event name if needed
    }

    // Data to broadcast with the event
    public function broadcastWith()
    {
        return [
            'unreadCount' => $this->unreadCount, // Send the unread count data
        ];
    }
}
