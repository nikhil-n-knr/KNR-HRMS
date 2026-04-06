<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\ContactSegment;

class ContactSegmentLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'all_segments' => $this->loadAllSegments($request),
            'smart_segments' => $this->loadSmartSegments($request),
            default => $this->loadAllSegments($request),
        };
    }

    private function loadAllSegments(Request $request)
    {
        $tenantId = $request->user()?->tenant_id ?? 1;
        return [
            'segments' => ContactSegment::where('tenant_id', $tenantId)->get(),
            'total_audience' => 12450,
            'active_segment_sync' => '4m ago'
        ];
    }

    private function loadSmartSegments(Request $request)
    {
        return [
            'smart_rules' => [
                ['name' => 'High-LTV Prediction', 'criteria' => 'Score > 85'],
                ['name' => 'Churn-Risk Signal', 'criteria' => 'Logins < 2 (7d)']
            ]
        ];
    }
}
