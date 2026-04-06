<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WfhRequest;
use Illuminate\Support\Str;

class BackfillWfhBatches extends Seeder
{
    public function run()
    {
        $count = WfhRequest::whereNull('batch_id')->count();
        if ($count === 0) {
            $this->command->info('No WFH requests with null batch_id found.');
            return;
        }

        $this->command->info("Backfilling $count records...");

        WfhRequest::whereNull('batch_id')->chunk(100, function ($requests) {
            foreach ($requests as $request) {
                // Assign unique Batch ID to EACH old request (preserving individual status)
                // Unless we want to group them by Created At? 
                // User said "I raised nearly 5 requests".
                // Ideally, group by (user_id, created_at, reason).
                // For now, let's just make them unique groups so they show up as 5 rows.
                $request->batch_id = (string) Str::uuid();
                $request->save();
            }
        });

        $this->command->info('Backfill complete.');
    }
}
