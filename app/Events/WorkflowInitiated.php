<?php

namespace App\Events;

use App\Models\Approval;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowInitiated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $approval;

    /**
     * Create a new event instance.
     */
    public function __construct(Approval $approval)
    {
        $this->approval = $approval;
    }
}
