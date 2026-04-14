<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use App\Models\Project;
use App\Models\User;

class ProjectProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $project;
    public $changedBy;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(Project $project, User $changedBy, $reason = null)
    {
        $this->project = $project;
        $this->changedBy = $changedBy;
        $this->reason = $reason;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('projects.' . $this->project->id),
        ];
    }

    public function broadcastAs()
    {
        return 'progress.updated';
    }
}
