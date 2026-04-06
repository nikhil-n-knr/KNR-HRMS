<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Direct DB insert for maximum speed/minimal memory
        DB::table('activity_logs')->insert([
            'log_name' => $this->data['log_name'] ?? 'default',
            'description' => $this->data['description'],
            'subject_type' => $this->data['subject_type'] ?? null,
            'subject_id' => $this->data['subject_id'] ?? null,
            'causer_type' => $this->data['causer_type'] ?? null,
            'causer_id' => $this->data['causer_id'] ?? null,
            'properties' => isset($this->data['properties']) ? json_encode($this->data['properties']) : null,
            'ip_address' => $this->data['ip_address'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
