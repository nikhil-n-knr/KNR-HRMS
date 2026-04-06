<?php

namespace App\Services\DevOps\Adapters;

interface GitAdapterInterface
{
    /**
     * Test connection to the provider.
     * @return bool
     */
    public function testConnection();

    /**
     * Get list of repositories accessible by the token.
     * @return array In unified format: [['id' => '...', 'name' => '...', 'url' => '...']]
     */
    public function getRepositories();

    /**
     * Get branches for a specific repository.
     * @param string $repoExternalId
     * @return array
     */
    public function getBranches($repoExternalId);
    
    /**
     * Set up a webhook for a repository.
     * @param string $repoIdentifier The identifier (e.g. owner/repo or ID)
     * @param string $webhookUrl The URL to receive payloads
     * @param string|null $secret Optional secret for payload verification
     * @return bool
     */
    public function setupWebhook($repoIdentifier, $webhookUrl, $secret = null);
}
