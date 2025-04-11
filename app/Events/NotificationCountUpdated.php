<?php
namespace App\Events;

// use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\Channel;
// use Illuminate\Foundation\Events\Dispatchable;
// use Illuminate\Queue\SerializesModels;


// class NotificationCountUpdated
// {
//     use Dispatchable, InteractsWithSockets, SerializesModels;

//     public $unreadCount;

//     public function __construct($unreadCount)
//     {
//         $this->unreadCount = $unreadCount;
//     }

//     public function broadcastOn()
//     {
//         return new Channel('notification.count');
//     }

//     public function broadcastAs()
//     {
//         return 'notifications'; 
//     }

//     public function broadcastWith()
//     {
//         return [
//             'unreadCount' => $this->unreadCount,
//         ];
//     }
// }


use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationCountUpdated implements ShouldBroadcast
{
    public $userId;
    public $unreadCount;

    public function __construct($userId, $unreadCount)
    {
        $this->userId = $userId;
        $this->unreadCount = $unreadCount;
    }

    public function broadcastOn()
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
