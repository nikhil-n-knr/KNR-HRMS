<?php

namespace App\Events;

use App\Models\VisitorPass;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitorCheckedIn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $visitorPass;

    /**
     * Create a new event instance.
     */
    public function __construct(VisitorPass $visitorPass)
    {
        $this->visitorPass = $visitorPass;
    }
}
