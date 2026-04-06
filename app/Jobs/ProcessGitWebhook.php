<?php

namespace App\Jobs;

use App\Models\GitCommit;
use App\Models\GitPullRequest;
use App\Models\GitRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessGitWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $provider;
    protected $eventType;
    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct($provider, $eventType, $payload)
    {
        $this->provider = $provider;
        $this->eventType = $eventType;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Identify Repository
        // GitHub: payload.repository.id (integer) or payload.repository.clone_url
        $repoData = $this->payload['repository'] ?? null;
        if (!$repoData) {
            Log::warning("ProcessGitWebhook: No repository data found in payload.");
            return;
        }

        $externalId = (string) ($repoData['id'] ?? $repoData['uuid'] ?? '');
        
        // Find Local Repo
        $repo = GitRepository::where('external_id', $externalId)->first();
        if (!$repo) {
            // Try by clone URL if external ID missing (fallback)
            // or maybe we haven't mapped it yet.
            // If not mapped, we might choose to create it or ignore.
            // Requirement usually: Ignore unmapped repos to filter noise.
            Log::info("ProcessGitWebhook: Repo not found for ID {$externalId}. Ignoring.");
            return;
        }

        // 2. Route Event
        try {
            switch ($this->eventType) {
                case 'push':
                    $this->handlePush($repo);
                    break;
                case 'pull_request':
                    $this->handlePullRequest($repo);
                    break;
                case 'pull_request_review':
                    $this->handleReview($repo);
                    break;
                // Bitbucket uses 'repo:push', 'pullrequest:created' etc.
                // Normalization required for multi-provider. Assuming GitHub format for now per "GitHubAdapter".
                default:
                    Log::info("ProcessGitWebhook: Unhandled event type {$this->eventType}");
            }
        } catch (\Exception $e) {
            Log::error("ProcessGitWebhook Failed: " . $e->getMessage());
        }
    }

    protected function handlePush($repo)
    {
        $commits = $this->payload['commits'] ?? [];
        foreach ($commits as $commitData) {
            // Calculate Stats (GitHub push payload may not have add/del stats per commit list, 
            // usually you need to fetch individual commit URL or standard payload has simple summary.
            // Detailed push payload often lacks additions/deletions. 
            // We might need to query API if critical. 
            // For now, check if payload has it (sometimes 'added', 'removed', 'modified' arrays).
            $added = count($commitData['added'] ?? []);
            $removed = count($commitData['removed'] ?? []);
            // This is file count, not line count. Line count requires API call.
            // For MVP velocity, 1 commit = 1 count. 
            // If we want lines, we'd queue a SyncCommitJob. 
            // Let's settle for basic info now.
            
            GitCommit::updateOrCreate(
                ['git_repository_id' => $repo->id, 'hash' => $commitData['id']],
                [
                    'message' => $commitData['message'],
                    'author_name' => $commitData['author']['name'],
                    'author_email' => $commitData['author']['email'],
                    'committed_at' => Carbon::parse($commitData['timestamp']),
                    'additions' => 0, // Placeholder
                    'deletions' => 0  // Placeholder
                ]
            );
        }
    }

    protected function handlePullRequest($repo)
    {
        $prData = $this->payload['pull_request'];
        
        $pr = GitPullRequest::updateOrCreate(
            ['git_repository_id' => $repo->id, 'external_id' => $prData['id']],
            [
                'title' => $prData['title'],
                'state' => $prData['state'],
                'author_name' => $prData['user']['login'],
                'created_at_provider' => Carbon::parse($prData['created_at']),
                'merged_at' => $prData['merged_at'] ? Carbon::parse($prData['merged_at']) : null,
                'closed_at' => $prData['closed_at'] ? Carbon::parse($prData['closed_at']) : null,
                'additions' => $prData['additions'] ?? 0,
                'deletions' => $prData['deletions'] ?? 0,
            ]
        );

        // Trigger Automation
        try {
            (new \App\Services\ProjectManagement\TaskAutomationService())->handlePrUpdate($pr);
        } catch (\Exception $e) {
            Log::error("Task Automation Failed: " . $e->getMessage());
        }
    }

    protected function handleReview($repo)
    {
        $review = $this->payload['review'];
        $pr = $this->payload['pull_request'];
        
        // Find PR
        $prModel = GitPullRequest::where('git_repository_id', $repo->id)
            ->where('external_id', $pr['id'])
            ->first();

        if ($prModel) {
            \App\Models\GitPrReview::updateOrCreate(
                [
                    'git_pull_request_id' => $prModel->id,
                    'external_id' => $review['id']
                ],
                [
                    'reviewer_name' => $review['user']['login'], // GitHub Login
                    'reviewer_username' => $review['user']['login'],
                    'state' => strtoupper($review['state']), // approved, changes_requested -> APPROVED
                    'submitted_at' => Carbon::parse($review['submitted_at']),
                    // Link employee if mapped (To be implemented via GitUserMap)
                ]
            );
        }
    }
}
