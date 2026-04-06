<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateEmployeeStateMappingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $location;

    /**
     * Create a new job instance.
     */
    public function __construct($location)
    {
        $this->location = $location;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Illuminate\Support\Facades\Log::info("Mapping compliance rules for employees at location: " . $this->location->name . " (State: " . $this->location->state_code . ")");
        
        // Update the compliance state for all employees assigned to this location
        // This assumes an 'employees' table with a 'location_id' and 'compliance_state' column
        \Illuminate\Support\Facades\DB::table('employees')
            ->where('location_id', $this->location->id)
            ->update(['compliance_state' => $this->location->state_code]);

        // Clear any payroll calculation cache for these employees if necessary
        \Illuminate\Support\Facades\Cache::tags(['payroll', 'compliance'])->flush();
    }
}
