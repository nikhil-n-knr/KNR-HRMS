<?php

namespace App\Services\DevOps;

use App\Models\GitProvider;
use App\Services\DevOps\Adapters\GitHubAdapter;
// use App\Services\DevOps\Adapters\BitbucketAdapter;

class GitProviderFactory
{
    public static function make(GitProvider $provider)
    {
        switch ($provider->type) {
            case 'github':
                return new GitHubAdapter($provider->access_token, $provider->base_url);
            // case 'bitbucket':
            //     return new BitbucketAdapter($provider->access_token);
            default:
                throw new \Exception("Unsupported provider type: {$provider->type}");
        }
    }
}
