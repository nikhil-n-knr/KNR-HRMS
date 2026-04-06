<?php

namespace App\Services\DevOps\Adapters;

use Illuminate\Support\Facades\Http;

class GitHubAdapter implements GitAdapterInterface
{
    protected $token;
    protected $baseUrl = 'https://api.github.com';

    public function __construct($token, $baseUrl = null)
    {
        $this->token = $token;
        if ($baseUrl) $this->baseUrl = $baseUrl;
    }

    public function testConnection()
    {
        $response = Http::withToken($this->token)->get("{$this->baseUrl}/user");
        return $response->successful();
    }

    public function getRepositories()
    {
        // Fetch User repos + Org repos? default to user for now.
        $response = Http::withToken($this->token)->get("{$this->baseUrl}/user/repos?per_page=50&sort=updated");
        
        if ($response->failed()) return [];

        return collect($response->json())->map(function ($repo) {
            return [
                'id' => (string) $repo['id'],
                'name' => $repo['full_name'], // "owner/repo"
                'url' => $repo['html_url'],
                'clone_url' => $repo['clone_url'],
                'private' => $repo['private']
            ];
        })->toArray();
    }

    public function getBranches($repoExternalId)
    {
        // Repo ID in GitHub API usually needs owner/repo name, but we might store ID.
        // For simplicity, we assume we need to fetch by "owner/repo" name or lookup URL.
        // NOTE: external_id for GitHub is numeric ID, but API uses owner/repo typically.
        // We'll need to store full_name as identifiers or make immense calls.
        // Let's assume we store "owner/repo" as external_id for GitHub Adapter context if possible?
        // Actually, let's use the 'repositories/{id}/branches' endpoint if available, but usually it's repos/{owner}/{repo}/branches.
        // For this MVP, we return empty or implement lookup if needed.
        return [];
    }

    public function setupWebhook($repoIdentifier, $webhookUrl, $secret = null)
    {
        $response = Http::withToken($this->token)->post("{$this->baseUrl}/repos/{$repoIdentifier}/hooks", [
            'name' => 'web',
            'active' => true,
            'events' => ['push', 'pull_request', 'pull_request_review', 'pull_request_review_comment'],
            'config' => [
                'url' => $webhookUrl,
                'content_type' => 'json',
                'secret' => $secret ?? config('app.key')
            ]
        ]);

        return $response->successful();
    }
}
