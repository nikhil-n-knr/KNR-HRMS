<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\VisitorPass;
use App\Events\VisitorCheckedIn;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VisitorRoutingService
{
    /**
     * Process a new Walk-in based on dynamic routing logic.
     * 
     * @param array $data Validated request data
     * @return VisitorPass
     */
    public function processWalkIn(array $data)
    {
        // 1. Fetch Purpose Config
        $purpose = DB::table('visitor_purposes')->where('id', $data['purpose_id'])->first();
        $workflow = $purpose->workflow_type; // standard, recruitment, crm, contractor, vip
        
        $linkedCandidateId = null;
        $linkedLeadId = null;
        $status = 'Checked-In';
        $printBadge = true;

        // 2. Dynamic Workflow Routing
        if ($workflow === 'recruitment' && !empty($data['email'])) {
            $candidate = \App\Models\Candidate::firstOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => explode(' ', $data['name'])[0],
                    'last_name' => explode(' ', $data['name'])[1] ?? '',
                    'phone' => $data['phone'] ?? null,
                    'source' => 'Walk-In Candidate'
                ]
            );
            $linkedCandidateId = $candidate->id;
            // E.g. trigger recruitment panel notification
        }

        if ($workflow === 'vip') {
            $data['is_vip'] = true;
            // VIPs do not wait. Alert Hospitality team.
        }

        if ($workflow === 'delivery') {
            // Deliveries bypass badges, they just notify the host
            $printBadge = false;
        }

        if ($workflow === 'family') {
            // Family requires host approval. Pass remains Pre-Registered until host verifies in mobile app
            $status = 'Host-Approval-Pending';
        }

        // 2.5 Multi-Entry Prevention
        $existingPass = VisitorPass::whereHas('visitor', function($q) use ($data) {
                $q->where('phone', $data['phone']);
            })
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->first();

        if ($existingPass) {
            throw new \Exception("Visitor is already checked in (Pass: {$existingPass->pass_code}). Please check them out first.");
        }

        // 3. Find or Create Visitor
        $visitor = Visitor::firstOrCreate(
            ['phone' => $data['phone']],
            [
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'company' => $data['company'] ?? null,
                'host_id' => $data['host_id'],
                'linked_candidate_id' => $linkedCandidateId,
            ]
        );

        // 3.1 Handle Photo Upload (Base64 from Webcam)
        if (!empty($data['photo'])) {
            $imageData = $data['photo'];
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, etc.

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    throw new \Exception('invalid image type');
                }
                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    throw new \Exception('base64_decode failed');
                }
            } else {
                throw new \Exception('did not match data URI with image data');
            }

            $fileName = 'visitor_' . $visitor->id . '_' . time() . '.' . $type;
            $filePath = 'visitors/' . $fileName;
            Storage::disk('public')->put($filePath, $imageData);
            
            $visitor->update(['photo_path' => '/storage/' . $filePath]);
        }

        // Update latest details
        $visitor->update([
            'host_id' => $data['host_id'],
            'company' => $data['company'] ?? $visitor->company,
            'linked_candidate_id' => $linkedCandidateId ?? $visitor->linked_candidate_id
        ]);

        if ($visitor->is_blacklisted) {
            // Log to Denied Access Ledger
            DB::table('visitor_access_logs')->insert([
                'status' => 'Blacklisted Match',
                'visitor_name' => $data['name'],
                'phone' => $data['phone'],
                'reason' => 'Walk-in blocked due to active blacklist flag.',
                'visitor_id' => $visitor->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            throw new \Exception('Access Denied: This visitor is blacklisted.');
        }

        // 4. Create Pass
        $expectedDuration = $data['expected_duration'] ?? 60; // default 1 hour

        $pass = VisitorPass::create([
            'visitor_id' => $visitor->id,
            'event_id' => $data['event_id'] ?? null,
            'pass_code' => Str::upper(Str::random(8)),
            'check_in_at' => $status === 'Checked-In' ? now() : null,
            'visit_date' => now()->toDateString(),
            'status' => $status,
            'is_vip' => $data['is_vip'] ?? false,
            'host_vouched' => $data['host_vouched'] ?? false,
            'group_size' => $data['group_size'] ?? 1,
            'expected_duration' => $expectedDuration,
            'material_details' => !empty($data['materials']) ? json_encode($data['materials']) : null,
            'meta_data' => [
                'wifi_code' => 'GUEST-' . rand(1000, 9999),
                'purpose_name' => $purpose->name,
                'workflows_run' => [$workflow],
                'print_badge' => $printBadge // Frontend logic hook
            ]
        ]);

        // 5. Fire Event (Asynchronous Dispatching of Jobs)
        if ($status === 'Checked-In') {
            event(new VisitorCheckedIn($pass));
        }

        return $pass;
    }
}
