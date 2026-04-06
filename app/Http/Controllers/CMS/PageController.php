<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CMS\Page;
use App\Models\CMS\PageVersion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    protected function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    protected function activeSiteId(): ?int
    {
        return DB::table('cms_sites')
            ->where('tenant_id', $this->tenantId())
            ->orderBy('id')
            ->value('id');
    }

    public function index(Request $request)
    {
        $siteId = $request->get('site_id', $this->activeSiteId());
        $pages = Page::where('tenant_id', $this->tenantId())
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('priority')
            ->get();

        return response()->json($pages);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'     => 'nullable|integer',
            'title'       => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'template'    => 'nullable|string',
            'status'      => 'nullable|in:draft,published',
        ]);

        $data['tenant_id']   = $this->tenantId();
        $data['site_id']   ??= $this->activeSiteId();
        $data['slug']        = $data['slug'] ?? Str::slug($data['title']);
        $data['status']    ??= 'draft';
        $data['layout_data'] = ['blocks' => []];
        $data['seo_meta']    = ['title' => $data['title'], 'description' => '', 'og_image' => null];
        $data['priority']    = Page::where('site_id', $data['site_id'])->max('priority') + 1;

        $page = Page::create($data);

        // Create initial version
        PageVersion::create([
            'tenant_id'      => $this->tenantId(),
            'page_id'        => $page->id,
            'layout_data'    => $page->layout_data,
            'created_by'     => auth()->id(),
            'commit_message' => 'Initial version',
        ]);

        return response()->json($page, 201);
    }

    public function show(string $id)
    {
        $page = Page::where('tenant_id', $this->tenantId())->findOrFail($id);
        $page->load('versions');
        return response()->json($page);
    }

    public function update(Request $request, string $id)
    {
        $page = Page::where('tenant_id', $this->tenantId())->findOrFail($id);
        $data = $request->validate([
            'title'  => 'nullable|string|max:255',
            'slug'   => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
        ]);

        if (isset($data['title'])) {
            $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        }

        $page->update($data);
        return response()->json($page);
    }

    public function destroy(string $id)
    {
        $page = Page::where('tenant_id', $this->tenantId())->findOrFail($id);
        $page->delete();
        return response()->json(['message' => 'Page deleted.']);
    }

    /**
     * Save the canvas block layout for a page.
     * Also auto-creates a version snapshot.
     */
    public function saveBlocks(Request $request, string $id)
    {
        $page = Page::where('tenant_id', $this->tenantId())->findOrFail($id);

        $data = $request->validate([
            'blocks'   => 'required|array',
        ]);

        // Snapshot version before overwriting
        PageVersion::create([
            'tenant_id'      => $this->tenantId(),
            'page_id'        => $page->id,
            'layout_data'    => $page->layout_data, // old content → versioned
            'created_by'     => auth()->id(),
            'commit_message' => 'Canvas save',
        ]);

        $page->update(['layout_data' => $data]);

        // Prune old versions → keep last 20
        $versionsToDelete = PageVersion::where('page_id', $page->id)
            ->orderByDesc('id')
            ->skip(20)
            ->get()
            ->pluck('id');
        PageVersion::whereIn('id', $versionsToDelete)->delete();

        return response()->json(['saved' => true]);
    }

    /**
     * Publish / unpublish a page.
     */
    public function publish(Request $request, string $id)
    {
        $page = Page::where('tenant_id', $this->tenantId())->findOrFail($id);
        $page->update(['status' => $page->status === 'published' ? 'draft' : 'published']);
        return response()->json(['status' => $page->status]);
    }

    /**
     * Update SEO meta for a page.
     */
    public function saveSeo(Request $request, string $id)
    {
        $page = Page::where('tenant_id', $this->tenantId())->findOrFail($id);
        $seo = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'keywords'    => 'nullable|string',
            'og_image'    => 'nullable|string',
            'schema'      => 'nullable|array',
        ]);
        $page->update(['seo_meta' => $seo]);
        return response()->json(['saved' => true]);
    }

    /**
     * Clone a page (duplicate).
     */
    public function clone(string $id)
    {
        $page = Page::where('tenant_id', $this->tenantId())->findOrFail($id);
        $clone = $page->replicate();
        $clone->title = $page->title . ' (Copy)';
        $clone->slug  = $page->slug . '-copy-' . Str::random(4);
        $clone->status = 'draft';
        $clone->save();
        return response()->json($clone, 201);
    }

    /**
     * Restore a page to a specific version.
     */
    public function restoreVersion(string $versionId)
    {
        $version = PageVersion::findOrFail($versionId);
        $page    = Page::where('tenant_id', $this->tenantId())->findOrFail($version->page_id);

        // Snapshot current content before restoring
        PageVersion::create([
            'tenant_id'      => $this->tenantId(),
            'page_id'        => $page->id,
            'layout_data'    => $page->layout_data,
            'created_by'     => auth()->id(),
            'commit_message' => 'Auto-snapshot before restore to v' . $versionId,
        ]);

        $page->update(['layout_data' => $version->layout_data]);
        return response()->json(['restored' => true, 'layout_data' => $page->layout_data]);
    }

    /**
     * List all versions of a page for the version history panel.
     */
    public function versions(string $pageId)
    {
        $page = Page::where('tenant_id', $this->tenantId())->findOrFail($pageId);

        $versions = PageVersion::where('page_id', $page->id)
            ->orderByDesc('id')
            ->with('savedBy:id,name')
            ->get()
            ->map(function ($v, $i) {
                return [
                    'id'              => $v->id,
                    'page_id'         => $v->page_id,
                    'version_number'  => $v->id,  // use id as version number
                    'label'           => $v->commit_message,
                    'change_summary'  => $v->commit_message,
                    'created_by_name' => $v->savedBy?->name ?? 'System',
                    'created_at'      => $v->created_at,
                    'layout_data'     => $v->layout_data,
                ];
            });

        return response()->json($versions);
    }
    /**
     * Bulk prune old versions across all pages of the tenant.
     * Keeps only the 20 most recent versions per page.
     */
    public function pruneVersions()
    {
        $pages = Page::where('tenant_id', $this->tenantId())->get();
        $totalDeleted = 0;

        foreach ($pages as $page) {
            $versionsToDelete = PageVersion::where('page_id', $page->id)
                ->orderByDesc('id')
                ->skip(20)
                ->get()
                ->pluck('id');
            
            if ($versionsToDelete->isNotEmpty()) {
                $count = PageVersion::whereIn('id', $versionsToDelete)->delete();
                $totalDeleted += $count;
            }
        }

        return response()->json(['pruned' => true, 'deleted_count' => $totalDeleted]);
    }
}
