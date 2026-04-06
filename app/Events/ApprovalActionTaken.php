<?php

namespace App\Events;

use App\Models\ApprovalStep;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalActionTaken
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $step;

    /**
     * Create a new event instance.
     */
    public function __construct(ApprovalStep $step)
    {
        $this->step = $step;
    }
}
