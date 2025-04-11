<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
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

    public function broadcastAs()
    {
        return 'notifications'; 
    }

    public function broadcastWith()
    {
        return [
            'unreadCount' => $this->unreadCount,
        ];
    }
}
