<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\Visitor;
use App\Models\VisitorPass;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\SendEventInvitationJob;

class BulkGuestImporter
{
    /**
     * Processes a CSV guest list and creates visitor records in chunks.
     */
    public function execute(Event $event, string $filePath)
    {
        $handle = fopen(Storage::disk('local')->path($filePath), 'r');
        $headers = fgetcsv($handle); // Assuming: Name, Email, Phone, Company

        $batch = [];
        $chunkSize = 100;
        $processedCount = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue; // Basic validation

            $data = [
                'name'    => $row[0],
                'email'   => $row[1],
                'phone'   => $row[2] ?? null,
                'company' => $row[3] ?? null,
            ];

            // 1. Create/Find Visitor
            $visitor = Visitor::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'company' => $data['company']
                ]
            );

            // 2. Create Pass
            $pass = VisitorPass::create([
                'visitor_id' => $visitor->id,
                'event_id'   => $event->id,
                'pass_code'  => Str::upper(Str::random(8)),
                'status'     => $visitor->is_blacklisted ? 'Blacklisted' : 'Pre-Registered',
                'visit_date' => $event->start_time->toDateString(),
            ]);

            // 3. Security Check & Queue Invitation
            if ($visitor->is_blacklisted) {
                \Illuminate\Support\Facades\DB::table('visitor_access_logs')->insert([
                    'status' => 'Bulk Import Blocked',
                    'visitor_name' => $visitor->name,
                    'phone' => $visitor->phone,
                    'reason' => "Bulk import attempt for event [ID: {$event->id}] blocked due to active blacklist flag.",
                    'visitor_id' => $visitor->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                SendEventInvitationJob::dispatch($pass);
                $processedCount++;
            }
        }

        fclose($handle);
        Storage::disk('local')->delete($filePath);

        return $processedCount;
    }
}
