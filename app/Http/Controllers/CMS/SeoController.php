<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeoController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    /**
     * Run a quick SEO audit for a page.
     */
    public function audit(Request $request)
    {
        $data = $request->validate([
            'page_id' => 'required|integer',
            'seo_meta' => 'nullable|string', // JSON string from frontend
        ]);

        $page = DB::table('cms_pages')->where('id', $data['page_id'])->first();
        abort_if(!$page, 404);

        // If seo_meta is provided, save it first
        if ($request->has('seo_meta')) {
            DB::table('cms_pages')->where('id', $data['page_id'])->update([
                'seo_meta' => $data['seo_meta'],
                'updated_at' => now(),
            ]);
            $page = DB::table('cms_pages')->where('id', $data['page_id'])->first();
        }

        $seo    = json_decode($page->seo_meta ?? '{}', true);
        $layout = json_decode($page->layout_data ?? '{}', true);
        $blocks = $layout['blocks'] ?? [];

        $issues      = [];
        $suggestions = [];
        $score       = 100;

        // Check meta title
        $title = $seo['title'] ?? '';
        if (empty($title)) {
            $issues[] = ['type' => 'critical', 'label' => 'Missing Meta Title'];
            $score   -= 20;
        } elseif (strlen($title) < 30) {
            $issues[] = ['type' => 'warning', 'label' => 'Meta title too short (< 30 chars)'];
            $score   -= 5;
            $suggestions[] = 'Add more descriptive keywords to your title.';
        } elseif (strlen($title) > 70) {
            $issues[] = ['type' => 'warning', 'label' => 'Meta title too long (> 70 chars)'];
            $score   -= 5;
        }

        // Check meta description
        $desc = $seo['description'] ?? '';
        if (empty($desc)) {
            $issues[] = ['type' => 'critical', 'label' => 'Missing Meta Description'];
            $score   -= 15;
            $suggestions[] = 'Add a 150-160 character meta description.';
        } elseif (strlen($desc) < 100) {
            $issues[] = ['type' => 'warning', 'label' => 'Meta description too short'];
            $score   -= 5;
        }
        
        // Check H1 block
        $hasH1 = collect($blocks)->contains(fn($b) => in_array($b['type'] ?? '', ['hero', 'heading']));
        if (!$hasH1) {
            $issues[] = ['type' => 'warning', 'label' => 'No H1 heading detected on page'];
            $score   -= 10;
        }

        // Check content length
        $totalText = collect($blocks)->map(fn($b) => (string)($b['content']['text'] ?? ''))->join(' ');
        if (str_word_count($totalText) < 100) {
            $issues[] = ['type' => 'warning', 'label' => 'Thin content (< 100 words)'];
            $score   -= 10;
            $suggestions[] = 'Add more text content to rank better.';
        }

        $score = max(0, min(100, $score));

        // Update page score
        DB::table('cms_pages')->where('id', $data['page_id'])->update(['seo_score' => $score]);

        return response()->json(['score' => $score, 'issues' => $issues, 'suggestions' => $suggestions]);
    }

    /**
     * AI-generate SEO meta from page content.
     * In production, calls Gemini API. For now returns a smart template.
     */
    public function generateMeta(Request $request)
    {
        $data = $request->validate(['page_id' => 'required|integer', 'site_id' => 'required|integer']);
        $page = DB::table('cms_pages')->where('id', $data['page_id'])->first();
        abort_if(!$page, 404);

        $layout  = json_decode($page->layout_data ?? '{}', true);
        $blocks  = $layout['blocks'] ?? [];
        $text    = collect($blocks)->map(fn($b) => $b['content']['text'] ?? $b['content']['title'] ?? '')->filter()->join(' ');
        $site    = DB::table('cms_sites')->where('id', $data['site_id'])->first();

        // Stub — integrate Gemini API here for real AI generation
        $title       = substr($page->title . ' – ' . ($site->name ?? 'Our Site'), 0, 70);
        $description = 'Discover ' . strtolower($page->title) . '. ' . substr(strip_tags($text), 0, 130) . '...';

        return response()->json([
            'title'       => $title,
            'description' => $description,
            'keywords'    => implode(', ', array_slice(explode(' ', strtolower($text)), 0, 8)),
        ]);
    }

    /**
     * Generate XML sitemap for the site.
     */
    public function generateSitemap(Request $request)
    {
        $data   = $request->validate(['site_id' => 'required|integer']);
        $site   = DB::table('cms_sites')->where('id', $data['site_id'])->first();
        $pages  = DB::table('cms_pages')->where('site_id', $data['site_id'])->where('status', 'published')->get();

        $domain = $site->domain ?? 'https://yourdomain.com';
        $xml    = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml   .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $url  = rtrim($domain, '/') . '/' . ltrim($page->slug, '/');
            $xml .= "  <url><loc>{$url}</loc><lastmod>" . date('Y-m-d', strtotime($page->updated_at)) . "</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>\n";
        }

        $xml .= '</urlset>';

        // In production: write to public/sitemap.xml
        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
