<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectManagement\GitAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitWebhookController extends Controller
{
    protected $gitService;

    public function __construct(GitAutomationService $gitService)
    {
        $this->gitService = $gitService;
    }

    public function handle(Request $request, $provider = 'github')
    {
        // 1. Verify Signature (Simplified for demo, but crucial for prod)
        // $signature = $request->header('X-Hub-Signature-256');
        // Retrieve project based on repository URL in payload
        
        $payload = $request->all();
        $repoUrl = $payload['repository']['html_url'] ?? null;

        if (!$repoUrl) {
            return response()->json(['message' => 'Repository URL missing'], 400);
        }

        $project = Project::where('repository_url', $repoUrl)->first();

        if (!$project) {
            Log::warning("GitWebhook: Project not found for repo $repoUrl");
            return response()->json(['message' => 'Project not found'], 404);
        }

        // 2. Verify Secret (if configured)
        if ($project->webhook_secret) {
            // detailed signature verification logic here
        }

        $event = $request->header('X-GitHub-Event');

        try {
            if ($event === 'push') {
                $this->gitService->processPush($payload, $project);
            } elseif ($event === 'pull_request') {
                $this->gitService->processPullRequest($payload, $project);
            }
        } catch (\Exception $e) {
            Log::error("GitWebhook Error: " . $e->getMessage());
            return response()->json(['message' => 'Internal Error'], 500);
        }

        return response()->json(['message' => 'Webhook processed']);
    }
}
