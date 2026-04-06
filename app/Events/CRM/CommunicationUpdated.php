<?php

namespace App\Events\CRM;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue; // Added for InteractsWithQueue

class CommunicationUpdated implements \Illuminate\Contracts\Broadcasting\ShouldBroadcast
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $data;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct($userId, array $data)
    {
        $this->userId = $userId;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\PrivateChannel('user.' . $this->userId),
            new \Illuminate\Broadcasting\PrivateChannel('tenant.' . auth()->user()->tenant_id ?? 'global'),
        ];
    }
}
