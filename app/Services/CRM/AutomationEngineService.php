<?php

namespace App\Services\CRM;

use App\Models\CRM\Contact;
use App\Models\CRM\MarketingAutomation;
use App\Models\CRM\AutomationStep;
use App\Models\CRM\AutomationExecution;
use Carbon\Carbon;
use App\Models\CRM\MarketingCampaign;
use App\Jobs\CRM\SendCampaignEmail;

class AutomationEngineService
{
    /**
     * Enroll a contact into an automation
     */
    public function enroll(Contact $contact, string $triggerType)
    {
        $automations = MarketingAutomation::where('tenant_id', $contact->tenant_id)
            ->where('is_active', true)
            ->where('trigger_type', $triggerType)
            ->get();

        foreach ($automations as $automation) {
            // Check if already enrolled
            $exists = AutomationExecution::where('automation_id', $automation->id)
                ->where('contact_id', $contact->id)
                ->where('status', 'active')
                ->exists();

            if (!$exists) {
                $execution = AutomationExecution::create([
                    'automation_id' => $automation->id,
                    'contact_id' => $contact->id,
                    'status' => 'active',
                ]);

                $this->processNextStep($execution);
            }
        }
    }

    /**
     * Process the next step for an execution
     */
    public function processNextStep(AutomationExecution $execution)
    {
        if ($execution->status !== 'active') return;

        $automation = $execution->automation;
        $currentStep = $execution->currentStep;

        $nextStep = null;
        if (!$currentStep) {
            // Get first step
            $nextStep = $automation->steps()->first();
        } else {
            // Get next step by order
            $nextStep = $automation->steps()
                ->where('step_order', '>', $currentStep->step_order)
                ->first();
        }

        if (!$nextStep) {
            $execution->update(['status' => 'completed']);
            return;
        }

        $execution->update(['current_step_id' => $nextStep->id]);

        // Process step based on type
        switch ($nextStep->type) {
            case 'action':
                $this->executeAction($execution, $nextStep);
                // Immediately process next if it's an action (unless it completes or fails)
                $this->processNextStep($execution);
                break;

            case 'delay':
                $hours = $nextStep->config['hours'] ?? 1;
                $execution->update([
                    'next_execution_at' => Carbon::now()->addHours($hours)
                ]);
                break;

            case 'condition':
                // For now, simple conditions or skip
                $this->processNextStep($execution);
                break;
        }
    }

    protected function executeAction(AutomationExecution $execution, AutomationStep $step)
    {
        $contact = $execution->contact;
        $config = $step->config;

        switch ($step->action_type) {
            case 'send_email':
                $templateId = $config['template_id'] ?? null;
                if ($templateId) {
                    $this->sendAutomationEmail($contact, $templateId, $execution->automation_id);
                }
                break;

            case 'add_tag':
                // Logic to add tag
                break;
        }
    }

    protected function sendAutomationEmail(Contact $contact, $templateId, $automationId)
    {
        // Reuse CampaignRecipient logic or similar
        // For automation, we might want to track it as a mini-campaign or just a log
        \Log::info("Automation Email being sent to {$contact->email} from flow #{$automationId}");
        
        // Mocking dispatch for now as we don't have a 'Recipient' model for automations yet
        // In a real app, you'd create an 'AutomationLog' entry and dispatch the email job.
    }
}
