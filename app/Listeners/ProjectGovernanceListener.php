<?php

namespace App\Listeners;

use App\Events\DocumentActionEvent;
use App\Services\Communication\WhatsAppBusinessService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProjectGovernanceListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $whatsapp;

    /**
     * Create the event listener.
     */
    public function __construct(WhatsAppBusinessService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Handle the event.
     */
    public function handle(DocumentActionEvent $event): void
    {
        $doc = $event->document;
        $project = $doc->project;
        $client = $project->client;

        // Logic for WhatsApp Nudges
        if ($event->actionType === 'upload' && $doc->category === 'requirement') {
            $this->notifyClientOfNewRequirement($client, $project, $doc);
        }
    }

    /**
     * Nudge Client via WhatsApp when a new BRD/Requirement is uploaded
     */
    private function notifyClientOfNewRequirement($client, $project, $doc)
    {
        // Fetch client users with mobile numbers
        $contacts = $client->users()->whereNotNull('mobile')->get();

        foreach ($contacts as $user) {
            $this->whatsapp->sendTemplateMessage(
                $user->mobile,
                'project_document_nudge',
                [
                    ['type' => 'body', 'parameters' => [
                        ['type' => 'text', 'text' => $user->name],
                        ['type' => 'text', 'text' => $doc->name],
                        ['type' => 'text', 'text' => $project->name]
                    ]]
                ]
            );
        }
        
        Log::info("Project Governance: Dispatched WhatsApp nudges for document sign-off.", [
            'project' => $project->code,
            'doc' => $doc->name
        ]);
    }
}
