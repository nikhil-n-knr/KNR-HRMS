<?php

namespace App\Services\CRM;

use App\Models\CRM\AutomationRule;
use Illuminate\Support\Facades\Log;

class AutomationEngine
{
    /**
     * Dispatch an event to the automation engine.
     */
    public function dispatch($event, $entity)
    {
        Log::info("Automation Triggered: {$event} for entity " . get_class($entity) . " #{$entity->id}");

        $rules = AutomationRule::where('tenant_id', $entity->tenant_id)
            ->where('trigger_event', $event)
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            $this->processRule($rule, $entity);
        }
    }

    protected function processRule(AutomationRule $rule, $entity)
    {
        // 1. Evaluate Conditions (Improved check)
        if (!$this->evaluateConditions($rule->conditions, $entity)) {
            return;
        }

        // 2. Execute Actions
        $actions = is_array($rule->actions) ? $rule->actions : json_decode($rule->actions, true);
        if (empty($actions)) return;

        foreach ($actions as $action) {
            $this->executeAction($action, $entity, $rule);
        }
    }

    protected function evaluateConditions($conditions, $entity)
    {
        $conditionsList = is_array($conditions) ? $conditions : json_decode($conditions, true);
        if (empty($conditionsList)) return true;

        foreach ($conditionsList as $attribute => $expectedValue) {
            if ($entity->{$attribute} != $expectedValue) {
                return false;
            }
        }
        return true; 
    }

    protected function executeAction($action, $entity, $rule)
    {
        $type = $action['type'] ?? null;
        if (!$type) return;

        switch ($type) {
            case 'send_email':
                $this->handleEmailAction($action, $entity);
                break;
            case 'create_task':
                $this->handleTaskAction($action, $entity);
                break;
            case 'update_field':
                $entity->update([$action['field'] => $action['value']]);
                break;
            case 'notify_user':
                // Logic for real-time notification
                break;
        }
    }

    protected function handleEmailAction($action, $entity)
    {
        // Implementation for sending automated emails
        Log::info("Automation: Sending email via action " . ($action['template_id'] ?? 'default'));
    }

    protected function handleTaskAction($action, $entity)
    {
        // Implementation for auto-creating tasks
        Log::info("Automation: Creating task: " . ($action['subject'] ?? 'Follow up'));
    }
}
