<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncGitProviders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'devops:sync';

    /**
     * The console command description.
     */
    protected $description = 'Synchronize Git repositories as a fallback to webhooks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting DevOps Sync...');
        
        $repositories = \App\Models\GitRepository::where('is_active', true)->with('provider')->get();
        
        foreach ($repositories as $repo) {
            $this->info("Syncing repository: {$repo->name}");
            try {
                $adapter = \App\Services\DevOps\GitProviderFactory::make($repo->provider);
                
                // Fetch recent commits (fallback)
                if (method_exists($adapter, 'syncRepository')) {
                    $adapter->syncRepository($repo);
                } else {
                    $this->warn("Adapter for {$repo->provider->type} does not support full sync yet. Relying on webhooks.");
                }
                
                $repo->update(['last_synced_at' => now()]);
            } catch (\Exception $e) {
                $this->error("Failed to sync repo {$repo->name}: {$e->getMessage()}");
                \Illuminate\Support\Facades\Log::error("DevOps Sync Error [Repo {$repo->id}]: " . $e->getMessage());
            }
        }
        
        $this->info('DevOps Sync Completed.');
    }
}
