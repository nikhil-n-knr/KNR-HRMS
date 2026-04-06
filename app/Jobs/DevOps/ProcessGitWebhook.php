<?php

namespace App\Jobs\DevOps;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GitRepository;
use Illuminate\Support\Facades\Log;

class ProcessGitWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $repository;
    public $providerType;
    public $payload;
    public $headers;

    public function __construct(GitRepository $repository, $providerType, $payload, $headers)
    {
        $this->repository = $repository;
        $this->providerType = $providerType;
        $this->payload = $payload;
        $this->headers = $headers;
    }

    public function handle(): void
    {
        Log::info("Processing {$this->providerType} webhook for repo ID: {$this->repository->id}");
        
        $eventType = $this->getEventType();
        
        if ($eventType === 'push') {
            $this->handlePush();
        } elseif ($eventType === 'pull_request') {
            $this->handlePullRequest();
        }
        
        $this->repository->update(['last_synced_at' => now()]);
    }

    private function getEventType()
    {
        if ($this->providerType === 'github') {
            $ghEvent = $this->headers['x-github-event'][0] ?? '';
            if ($ghEvent === 'push') return 'push';
            if ($ghEvent === 'pull_request') return 'pull_request';
        }
        return 'unknown';
    }

    private function handlePush()
    {
        $commits = $this->payload['commits'] ?? [];
        foreach($commits as $commitData) {
            \App\Models\GitCommit::updateOrCreate(
                [
                    'git_repository_id' => $this->repository->id,
                    'hash' => $commitData['id']
                ],
                [
                    'branch' => str_replace('refs/heads/', '', $this->payload['ref'] ?? ''),
                    'author_name' => $commitData['author']['name'] ?? 'Unknown',
                    'author_email' => $commitData['author']['email'] ?? '',
                    'message' => $commitData['message'] ?? '',
                    'url' => $commitData['url'] ?? '',
                    'committed_at' => \Carbon\Carbon::parse($commitData['timestamp'] ?? now()),
                    'additions' => count($commitData['added'] ?? []) * 10,
                    'deletions' => count($commitData['removed'] ?? []) * 10,
                ]
            );
        }
    }

    private function handlePullRequest()
    {
        $prData = $this->payload['pull_request'] ?? [];
        if (empty($prData)) return;

        $state = 'open';
        if ($prData['state'] === 'closed' && !empty($prData['merged_at'])) {
            $state = 'merged';
        } elseif ($prData['state'] === 'closed') {
            $state = 'closed';
        }

        \App\Models\GitPullRequest::updateOrCreate(
            [
                'git_repository_id' => $this->repository->id,
                'external_id' => (string) ($prData['id'] ?? $prData['number'] ?? '')
            ],
            [
                'title' => $prData['title'] ?? '',
                'state' => $state,
                'author_name' => $prData['user']['login'] ?? 'Unknown',
                'url' => $prData['html_url'] ?? '',
                'source_branch' => $prData['head']['ref'] ?? '',
                'target_branch' => $prData['base']['ref'] ?? '',
                'created_at_provider' => \Carbon\Carbon::parse($prData['created_at'] ?? now()),
                'merged_at' => !empty($prData['merged_at']) ? \Carbon\Carbon::parse($prData['merged_at']) : null,
                'additions' => $prData['additions'] ?? 0,
                'deletions' => $prData['deletions'] ?? 0,
            ]
        );
    }
}
