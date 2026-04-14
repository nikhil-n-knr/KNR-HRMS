<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KnowledgeArticle;

class KnowledgeArticleSeeder extends Seeder
{
    public function run()
    {
        $articles = [
            [
                'title' => 'Cloud Infrastructure Governance',
                'slug' => 'cloud-infra-gov',
                'category' => 'Governance',
                'summary' => 'Deep-dive into our multi-cloud orchestration security protocols, encryption at rest, and automated compliance auditing signatures.',
                'content' => 'Full technical documentation for stakeholders...',
                'icon' => 'ShieldCheckIcon',
                'read_time' => 8,
                'is_featured' => true,
                'visibility' => 'client_shared'
            ],
            [
                'title' => 'Enterprise API Integration',
                'slug' => 'api-integration-guide',
                'category' => 'Technical',
                'summary' => 'Architecture signatures for high-throughput websocket channels and RESTful state management in distributed environments.',
                'icon' => 'CommandLineIcon',
                'read_time' => 12,
                'is_featured' => false,
                'visibility' => 'client_shared'
            ],
            [
                'title' => 'Stakeholder Protocol v2.5',
                'slug' => 'stakeholder-protocol',
                'category' => 'Guidelines',
                'summary' => 'Official operational guidelines for requirement sign-offs, deployment windows, and emergency signal escalation paths.',
                'icon' => 'DocumentTextIcon',
                'read_time' => 5,
                'is_featured' => true,
                'visibility' => 'client_shared'
            ]
        ];

        foreach ($articles as $article) {
            KnowledgeArticle::create($article);
        }
    }
}
