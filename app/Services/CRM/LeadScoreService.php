<?php

namespace App\Services\CRM;

use App\Models\CRM\Lead;
use App\Models\CRM\LeadScoreRule;

class LeadScoreService
{
    public function calculateScore(Lead $lead)
    {
        $rules = LeadScoreRule::where('tenant_id', $lead->tenant_id)
            ->where('is_active', true)
            ->get();

        $score = 0;

        foreach ($rules as $rule) {
            $fieldValue = $lead->{$rule->criteria_field} ?? null;
            
            if ($this->matchesCriteria($fieldValue, $rule->operator, $rule->value)) {
                $score += $rule->points;
            }
        }

        // Add base activity score if applicable, for now just rule based
        $lead->update(['score' => $score]);
        
        return $score;
    }

    private function matchesCriteria($fieldValue, $operator, $ruleValue)
    {
        if (is_null($fieldValue)) return false;

        $fieldValue = strtolower((string)$fieldValue);
        $ruleValue = strtolower((string)$ruleValue);

        switch ($operator) {
            case 'equals':
                return $fieldValue === $ruleValue;
            case 'contains':
                return str_contains($fieldValue, $ruleValue);
            case 'starts_with':
                return str_starts_with($fieldValue, $ruleValue);
            case 'ends_with':
                return str_ends_with($fieldValue, $ruleValue);
            case 'not_equals':
                return $fieldValue !== $ruleValue;
            default:
                return false;
        }
    }
}
