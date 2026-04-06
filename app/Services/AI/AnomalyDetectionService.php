<?php

namespace App\Services\AI;

use App\Models\Timesheet;
use App\Models\AiAnalysisLog;
use Illuminate\Support\Facades\Log;

class AnomalyDetectionService
{
    const MAX_DAILY_HOURS = 12;

    /**
     * Analyze a Timesheet for suspicious activity.
     */
    public function analyzeTimesheet(Timesheet $timesheet)
    {
        $issues = [];
        $score = 0.0; // Confidence that it IS an anomaly (0 to 1)

        // Rule 1: excessive hours
        if ($timesheet->hours_spent > self::MAX_DAILY_HOURS) {
            $issues[] = "Logged {$timesheet->hours_spent} hours, which exceeds the daily limit of " . self::MAX_DAILY_HOURS . ".";
            $score += 0.8;
        }

        // Rule 2: Future date
        if ($timesheet->date > now()->toDateString()) {
             $issues[] = "Logged time for a future date.";
             $score += 1.0;
        }

        if (count($issues) > 0) {
            $this->logAnomaly($timesheet, $issues, min($score, 1.0));
        }
    }

    private function logAnomaly($model, array $issues, float $confidence)
    {
        AiAnalysisLog::create([
            'analyzable_type' => get_class($model),
            'analyzable_id' => $model->id,
            'type' => 'Anomaly',
            'severity' => $confidence > 0.8 ? 'Warning' : 'Info',
            'confidence_score' => $confidence,
            'summary' => 'Potential Timesheet Anomaly Detected',
            'analysis_data' => ['issues' => $issues],
            'is_reviewed' => false
        ]);
        
        Log::warning('AI Anomaly Detected', ['model' => get_class($model), 'id' => $model->id, 'issues' => $issues]);
    }
}
