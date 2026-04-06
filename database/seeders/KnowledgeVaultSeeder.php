<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\VaultCategory;
use App\Models\VaultArticle;

class KnowledgeVaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::limit(3)->get();

        if ($projects->isEmpty()) {
            return; 
        }

        foreach ($projects as $project) {
            // Category 1: Onboarding
            $cat1 = VaultCategory::create([
                'project_id' => $project->id, 
                'name' => 'Nexus Onboarding & Guidelines', 
                'icon' => 'SparklesIcon', 
                'order' => 1
            ]);

            VaultArticle::create([
                'category_id' => $cat1->id,
                'title' => 'Welcome to your Engineering Command Center',
                'excerpt' => 'A strategic overview of the Nexus engagement platform.',
                'content' => '<h3>Platform Philosophy</h3><p>Nexus is designed for high-stakes engineering transparency. This portal is your direct link to our core systems, bypassing traditional ticketing delays.</p><h4>Operational Flow</h4><ul><li><strong>Broadcasts:</strong> Direct signal to developers.</li><li><strong>Vault:</strong> Instant access to prototypes and specs.</li><li><strong>Intelligence:</strong> Real-time ROI and technical debt tracking.</li></ul>',
                'order' => 1,
                'is_featured' => true,
                'is_client_visible' => true
            ]);

            VaultArticle::create([
                'category_id' => $cat1->id,
                'title' => 'Signal Priority Definitions (SLA)',
                'excerpt' => 'How we categorize and respond to your broadcasts.',
                'content' => '<p>We maintain a rigorous triage protocol to ensure critical blockers are addressed immediately.</p><ul><li><strong>Catastrophic:</strong> Immediate mobilization. Sub-15m assignment.</li><li><strong>High:</strong> 4h engagement window.</li><li><strong>Standard:</strong> Evaluated in next sprint planning session.</li></ul>',
                'order' => 2,
                'is_featured' => false,
                'is_client_visible' => true
            ]);

            // Category 2: Technical Architecture
            $cat2 = VaultCategory::create([
                'project_id' => $project->id, 
                'name' => 'Technical Blueprints', 
                'icon' => 'CpuChipIcon', 
                'order' => 2
            ]);

            VaultArticle::create([
                'category_id' => $cat2->id,
                'title' => 'Micro-Frontend Orchestration',
                'excerpt' => 'How your application remains modular and scalable.',
                'content' => '<h3>Architectural Integrity</h3><p>Your application uses a module-federation approach. This allows us to deploy updates to specific sub-modules without affecting global system uptime.</p>',
                'order' => 1,
                'is_featured' => true,
                'is_client_visible' => true
            ]);

            VaultArticle::create([
                'category_id' => $cat2->id,
                'title' => 'Security & Encryption Standards',
                'excerpt' => 'Overview of data protection layers.',
                'content' => '<p>All data at rest is encrypted using AES-256. Transit utilizes TLS 1.3. We perform weekly automated vulnerability scans against the staging cluster.</p>',
                'order' => 2,
                'is_featured' => false,
                'is_client_visible' => true
            ]);

            // Category 3: Environment Setup
            $cat3 = VaultCategory::create([
                'project_id' => $project->id, 
                'name' => 'Developer Environment', 
                'icon' => 'CommandLineIcon', 
                'order' => 3
            ]);

            VaultArticle::create([
                'category_id' => $cat3->id,
                'title' => 'Local Docker Setup Guide',
                'excerpt' => 'Step-by-step guide to cloning the codebase for local review.',
                'content' => '<p>To run your private instance locally:</p><pre class="bg-slate-900 text-white p-4 rounded mt-2">git clone [REPO_URL]\n./vendor/bin/sail up -d</pre><p>Detailed env vars are available in the Vault under "Secrets Management".</p>',
                'order' => 1,
                'is_featured' => false,
                'is_client_visible' => true
            ]);

            // Category 4: FAQ & Support
            $cat4 = VaultCategory::create([
                'project_id' => $project->id, 
                'name' => 'Operational FAQ', 
                'icon' => 'QuestionMarkCircleIcon', 
                'order' => 4
            ]);

            VaultArticle::create([
                'category_id' => $cat4->id,
                'title' => 'Requesting a Feature Expansion',
                'excerpt' => 'The process for moving from a bug fix to a new module.',
                'content' => '<p>If you have a requirement that goes beyond existing scope, use the "Expansion Request" button in your main dashboard to initiate a technical discovery phase.</p>',
                'order' => 1,
                'is_featured' => false,
                'is_client_visible' => true
            ]);
        }
    }
}
