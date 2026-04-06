<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgencySiteSeeder extends Seeder
{
    private int $tenantId = 1;
    private int $siteId;

    public function run(): void
    {
        // 1. Ensure a tenant exists
        if (!DB::table('tenants')->where('id', $this->tenantId)->exists()) {
            DB::table('tenants')->insert([
                'id'         => $this->tenantId,
                'name'       => 'Nexus Creative',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Create the static site
        $site = DB::table('cms_sites')
            ->where('tenant_id', $this->tenantId)
            ->where('slug', 'nexus-creative')
            ->first();

        if (!$site) {
            $this->siteId = DB::table('cms_sites')->insertGetId([
                'tenant_id'  => $this->tenantId,
                'name'       => 'Nexus Creative Agency',
                'slug'       => 'nexus-creative',
                'type'       => 'static',
                'status'     => 'live',
                'is_live'    => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->siteId = $site->id;
        }

        // 3. Create theme
        $themeId = DB::table('cms_themes')->insertGetId([
            'tenant_id'    => $this->tenantId,
            'name'         => 'Corporate Slate',
            'is_active'    => true,
            'css_framework'=> 'tailwind_v4',
            'colors'       => json_encode(['primary'=>'#0f172a','secondary'=>'#4f46e5','accent'=>'#3b82f6']),
            'typography'   => json_encode(['font_heading'=>'Inter','font_body'=>'Inter']),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        DB::table('cms_sites')->where('id', $this->siteId)->update(['theme_id' => $themeId]);

        // 4. Seed Data
        $this->seedHomePage();

        $this->command->info("✅ AgencySiteSeeder complete. Site ID: {$this->siteId}");
    }

    private function seedHomePage()
    {
        // Check if home page already exists for this site
        $homeId = DB::table('cms_pages')
            ->where('site_id', $this->siteId)
            ->where('slug', 'home')
            ->value('id');

        if (!$homeId) {
            $homeId = DB::table('cms_pages')->insertGetId([
                'tenant_id'  => $this->tenantId,
                'site_id'    => $this->siteId,
                'title'      => 'Nexus Agency Home',
                'slug'       => '/',
                'is_home'    => true,
                'status'     => 'published',
                'layout_data'=> json_encode(['blocks' => $this->getHomePageBlocks()]),
                'seo_meta'   => json_encode([
                    'title' => 'Nexus Creative Agency | Digital Excellence',
                    'description' => 'Award-winning design & engineering studio turning complex problems into elegant solutions.'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('cms_pages')->where('id', $homeId)->update([
                'layout_data'=> json_encode(['blocks' => $this->getHomePageBlocks()]),
            ]);
        }
    }

    private function getHomePageBlocks(): array
    {
        return [
            [
                'id'   => 'agency-hero',
                'type' => 'hero',
                'data' => [
                    'headline'   => 'We Build Digital Experiences',
                    'subheadline'=> 'Award-winning design & engineering studio turning complex problems into elegant solutions for forward-thinking brands.',
                    'cta_text'   => 'View Capabilities',
                    'cta_url'    => '/services',
                    'bg_image'   => 'https://loremflickr.com/1920/1080/office,team?lock=50',
                    'overlay'    => true,
                    'overlay_opacity' => 0.8,
                ]
            ],
            [
                'id'   => 'agency-features',
                'type' => 'features',
                'data' => [
                    'title'       => 'End-to-End Capabilities',
                    'subtitle'    => 'Our comprehensive approach ensures synergy across every touchpoint.',
                    'features'    => [
                        ['icon'=>'fas fa-laptop-code', 'title'=>'Digital Engineering', 'description'=>'Robust software architectures built for scale and security.'],
                        ['icon'=>'fas fa-paint-brush', 'title'=>'UX/UI Design', 'description'=>'Intuitive interfaces guided by human-centered design principles.'],
                        ['icon'=>'fas fa-bullseye', 'title'=>'Brand Strategy', 'description'=>'Positioning and identity frameworks that resonate with your audience.'],
                    ]
                ]
            ],
            [
                'id'   => 'agency-testimonial',
                'type' => 'testimonial',
                'data' => [
                    'quote'  => 'Nexus Creative completely transformed our digital presence. Their attention to engineering detail and design aesthetics is unparalleled in the industry.',
                    'author' => 'Sarah Jenkins',
                    'role'   => 'CTO, GlobalTech Corp',
                    'avatar' => 'https://loremflickr.com/200/200/business,woman?lock=8'
                ]
            ],
            [
                'id'   => 'agency-stats',
                'type' => 'stats',
                'data' => [
                    'title' => 'Proven Track Record',
                    'stats' => [ // Make sure to use the correct key as per StatsBlock formatting typically
                        ['value'=>'150+', 'label'=>'Projects Delivered'],
                        ['value'=>'12+',  'label'=>'Industry Awards'],
                        ['value'=>'98%',  'label'=>'Client Retention'],
                        ['value'=>'24/7', 'label'=>'Support Active'],
                    ]
                ]
            ],
            [
                'id'   => 'agency-cta',
                'type' => 'cta',
                'data' => [
                    'title'       => 'Ready to start your next project?', // Corrected standard key mapping usually
                    'description' => 'Let us discuss how we can help your business navigate the digital landscape.',
                    'btn_text'    => 'Get in Touch',
                    'btn_url'     => '/contact'
                ]
            ],
            [
                'id'   => 'agency-footer',
                'type' => 'footer',
                'data' => [
                    'company_name' => 'Nexus Creative Agency',
                    'description'  => 'Pioneering digital experiences since 2018.',
                    'address'      => '120 Innovation Drive, Tech District, NY 10001',
                    'email'        => 'hello@nexuscreative.dev',
                    'phone'        => '+1 (555) 123-4567',
                    'social'       => [
                        ['platform'=>'twitter', 'url'=>'https://twitter.com'],
                        ['platform'=>'linkedin', 'url'=>'https://linkedin.com'],
                        ['platform'=>'github', 'url'=>'https://github.com']
                    ]
                ]
            ]
        ];
    }
}
