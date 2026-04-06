<?php

namespace App\Services\CRM;

use App\Models\CRM\Lead;
use App\Models\CRM\LeadScoreRule;

class LeadScoringService
{
    /**
     * Calculate and update the score for a lead.
     */
    public function calculate(Lead $lead)
    {
        $rules = LeadScoreRule::where('tenant_id', $lead->tenant_id)
            ->where('is_active', true)
            ->get();

        $totalScore = 0;

        foreach ($rules as $rule) {
            if ($this->matchesRule($lead, $rule)) {
                $totalScore += $rule->points;
            }
        }

        $lead->update(['score' => $totalScore]);

        return $totalScore;
    }

    /**
     * Check if a lead matches a specific rule.
     */
    protected function matchesRule(Lead $lead, LeadScoreRule $rule)
    {
        $field = $rule->criteria_field;
        $operator = $rule->operator;
        $expected = $rule->value;
        $actual = $lead->{$field};

        switch ($operator) {
            case 'equals':
                return $actual == $expected;
            case 'not_equals':
                return $actual != $expected;
            case 'contains':
                return str_contains(strtolower($actual), strtolower($expected));
            case 'greater_than':
                return (float)$actual > (float)$expected;
            case 'less_than':
                return (float)$actual < (float)$expected;
            case 'is_empty':
                return empty($actual);
            case 'is_not_empty':
                return !empty($actual);
            default:
                return false;
        }
    }
}
