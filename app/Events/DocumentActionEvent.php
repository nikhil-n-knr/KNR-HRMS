<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\ProjectDocument;

class DocumentActionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $document;
    public $actionType;

    /**
     * Create a new event instance.
     */
    public function __construct(ProjectDocument $document, $actionType)
    {
        $this->document = $document;
        $this->actionType = $actionType; // upload, sign_off, delete
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('projects.' . $this->document->project_id),
        ];
    }

    public function broadcastAs()
    {
        return 'document.action';
    }
}
