<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\DevOps\ProcessGitWebhook;
use App\Models\GitRepository;

class GitWebhookController extends Controller
{
    public function handle(Request $request, $providerType)
    {
        $payload = $request->all();
        $headers = $request->headers->all();

        $repoExternalId = $this->extractRepoId($payload, $providerType);
        
        $repository = GitRepository::whereHas('provider', function($q) use ($providerType) {
            $q->where('type', $providerType);
        })->where('external_id', $repoExternalId)->first();

        if (!$repository || !$repository->is_active) {
            return response()->json(['status' => 'ignored', 'message' => 'Repo not configured or inactive'], 200);
        }

        // Dispatch Job to background
        ProcessGitWebhook::dispatch($repository, $providerType, $payload, $headers);

        return response()->json(['status' => 'queued']);
    }

    private function extractRepoId($payload, $providerType)
    {
        if ($providerType === 'github') return $payload['repository']['id'] ?? null;
        if ($providerType === 'gitlab') return $payload['project']['id'] ?? null;
        if ($providerType === 'bitbucket') return $payload['repository']['uuid'] ?? null;
        return null;
    }
}
