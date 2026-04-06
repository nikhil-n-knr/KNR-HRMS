<?php

namespace App\Services\LMS;

use App\Models\LMS\LmsEnrollment;
use App\Models\LMS\LmsInstitution;
use App\Models\LMS\LmsErpSyncLog;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ERP Integration Service
 * 
 * Handles bidirectional sync between the LMS and external Institute ERPs.
 * Supports: User Sync, Enrollment Sync, Grade Push, and Attendance Sync.
 */
class ErpIntegrationService
{
    /**
     * Start an import job for an institution.
     */
    public function importFromErp(LmsInstitution $institution, string $entityType)
    {
        $log = LmsErpSyncLog::create([
            'institution_id' => $institution->id,
            'direction' => 'import',
            'entity_type' => $entityType,
            'status' => 'running',
            'triggered_by' => auth()->id() ?? 1
        ]);

        try {
            $config = $institution->settings['erp_config'] ?? null;
            if (!$config) throw new \Exception("ERP Configuration missing for institution: " . $institution->id);

            // Fetch data from ERP API (Mocked logic for now)
            $response = $this->fetchFromErpApi($config, $entityType);
            
            $processed = 0;
            $failed = 0;
            $errors = [];

            foreach ($response as $item) {
                try {
                    $this->processImportItem($item, $entityType, $institution);
                    $processed++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = ["item" => $item, "error" => $e->getMessage()];
                }
            }

            $log->update([
                'status' => 'completed',
                'records_processed' => $processed,
                'records_failed' => $failed,
                'error_log' => $errors
            ]);

            return true;
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error_log' => ['message' => $e->getMessage()]]);
            Log::error("ERP Sync Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Push grades/completions back to the ERP.
     */
    public function pushGradesToErp(LmsEnrollment $enrollment)
    {
        $institution = $enrollment->institution;
        $config = $institution->settings['erp_config'] ?? null;

        if (!$config || !($config['grade_push_enabled'] ?? false)) return false;

        $payload = [
            'student_id' => $enrollment->metadata['erp_student_id'] ?? $enrollment->user->email,
            'course_id'  => $enrollment->course->erp_course_code ?? $enrollment->course->id,
            'grade'      => $enrollment->courseProgress->avg_quiz_score ?? 0,
            'status'     => $enrollment->status,
            'completed_at' => $enrollment->completed_at,
            'certificate_url' => route('lms.verify.certificate', ['code' => $enrollment->certificatesV2()->latest()->first()?->unique_code])
        ];

        try {
            Http::withHeaders(['X-ERP-API-KEY' => $config['api_key']])
                ->post($config['api_url'] . '/v1/grades', $payload);
            
            return true;
        } catch (\Exception $e) {
            Log::error("ERP Grade Push Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Internal: Process individual import items.
     */
    protected function processImportItem($item, $type, $institution)
    {
        switch ($type) {
            case 'user':
                $user = User::firstOrCreate(['email' => $item['email']], [
                    'name' => $item['name'],
                    'password' => bcrypt(Str::random(12)),
                    'role_id' => 3 // Learner Role
                ]);
                
                // Add LMS Role for this institution
                $user->lmsRoles()->updateOrCreate([
                    'institution_id' => $institution->id,
                    'role' => 'learner'
                ]);
                break;

            case 'enrollment':
                $user = User::where('email', $item['email'])->first();
                $course = \App\Models\LmsCourse::where('erp_course_code', $item['course_code'])->first();

                if ($user && $course) {
                    LmsEnrollment::updateOrCreate(
                        ['user_id' => $user->id, 'course_id' => $course->id],
                        [
                            'institution_id' => $institution->id,
                            'source' => 'erp_sync',
                            'metadata' => ['erp_student_id' => $item['student_id']]
                        ]
                    );
                }
                break;
        }
    }

    protected function fetchFromErpApi($config, $type)
    {
        // Mocked response representing typical ERP response
        return [];
    }
}
