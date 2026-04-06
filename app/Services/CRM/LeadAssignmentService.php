<?php

namespace App\Services\CRM;

use App\Models\CRM\Lead;
use App\Models\CRM\LeadAssignmentRule;

class LeadAssignmentService
{
    /**
     * Assign a lead based on active rules.
     */
    public function assign(Lead $lead)
    {
        $rules = LeadAssignmentRule::where('tenant_id', $lead->tenant_id)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($rules as $rule) {
            if ($this->matchesCriteria($lead, $rule->criteria)) {
                $lead->update([
                    'assigned_to' => $rule->assign_to_user_id
                ]);
                return $rule;
            }
        }

        return null;
    }

    /**
     * Simple criteria matcher.
     */
    protected function matchesCriteria(Lead $lead, array $criteria)
    {
        // Criteria format: [['field' => 'source', 'operator' => 'equals', 'value' => 'web']]
        foreach ($criteria as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? 'equals';
            $expected = $condition['value'] ?? null;

            if (!$field) continue;

            $actual = $lead->{$field};

            switch ($operator) {
                case 'equals':
                    if ($actual != $expected) return false;
                    break;
                case 'contains':
                    if (strpos(strtolower($actual), strtolower($expected)) === false) return false;
                    break;
                // Add more operators as needed
            }
        }

        return true;
    }
}
