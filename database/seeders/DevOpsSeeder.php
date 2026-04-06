<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\GitRepository;
use App\Models\GitCommit;
use App\Models\GitPullRequest;
use App\Models\GitPrReview;
use App\Models\Task;
use Carbon\Carbon;

class DevOpsSeeder extends Seeder
{
    public function run()
    {
        // 1. Ensure we have a project with a repository
        $tenant = \App\Models\Tenant::first();
        $project = Project::first() ?? Project::create([
            'name' => 'Demo Project', 
            'status' => 'active',
            'tenant_id' => $tenant->id
        ]);
        
        $provider = \App\Models\GitProvider::firstOrCreate(
            ['type' => 'github'],
            ['name' => 'Demo GitHub', 'access_token' => 'dummy', 'base_url' => null]
        );

        $repo = GitRepository::firstOrCreate(
            ['name' => 'demo-repo'],
            [
                'git_provider_id' => $provider->id, 
                'external_id' => '12345',
                'url' => 'https://github.com/acme/demo-repo',
                'project_id' => $project->id
            ]
        );

        $this->command->info("Seeding data for repo: {$repo->name}");

        // 2. Generate Commits (Last 30 days)
        $authors = ['John Doe', 'Jane Smith', 'Mike Dev', 'Sarah Ops'];
        
        GitCommit::where('git_repository_id', $repo->id)->delete(); // Clean slate

        for ($i = 0; $i < 50; $i++) {
            $date = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23));
            $author = $authors[array_rand($authors)];
            $additions = rand(10, 500);
            $deletions = rand(5, 200);
            
            // Randomly link to a task if tasks exist
            $message = "feat: implemented feature " . rand(100, 999);
            $taskId = null;
            
            if (rand(0, 10) > 7) { // 30% chance of task link
                $message .= " (Fixes #TASK-" . rand(1, 5) . ")"; 
            }

            GitCommit::create([
                'git_repository_id' => $repo->id,
                'hash' => md5(uniqid()),
                'message' => $message,
                'author_name' => $author,
                'author_email' => strtolower(str_replace(' ', '.', $author)) . '@acme.com',
                'committed_at' => $date,
                'additions' => $additions,
                'deletions' => $deletions
            ]);
        }

        // 3. Generate Pull Requests
        GitPullRequest::where('git_repository_id', $repo->id)->delete();
        
        for ($i = 0; $i < 10; $i++) {
            $created = Carbon::now()->subDays(rand(1, 15));
            $merged = (rand(0, 1) === 1) ? $created->copy()->addHours(rand(2, 48)) : null;
            $state = $merged ? 'merged' : 'open';
            
            GitPullRequest::create([
                'git_repository_id' => $repo->id,
                'external_id' => (string)($i + 100),
                'title' => "Feature Integration " . ($i+1),
                'state' => $state,
                'author_name' => $authors[array_rand($authors)],
                'created_at_provider' => $created,
                'merged_at' => $merged,
                'additions' => rand(50, 1000),
                'deletions' => rand(10, 300)
            ]);
        }

        $this->command->info("Seeded 50 commits and 10 PRs.");
    }
}
