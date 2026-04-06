<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbTestController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $tests = DB::table('cms_ab_tests')
            ->join('cms_pages', 'cms_ab_tests.page_id', '=', 'cms_pages.id')
            ->where('cms_ab_tests.tenant_id', $this->tenantId())
            ->select('cms_ab_tests.*', 'cms_pages.slug as page_slug', 'cms_pages.title as page_title')
            ->orderByDesc('cms_ab_tests.id')
            ->get();

        return response()->json($tests);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'page_id'  => 'required|exists:cms_pages,id',
            'goal'     => 'required|string',
            'variants' => 'required|string', // JSON string from frontend
        ]);

        $page = DB::table('cms_pages')->where('id', $data['page_id'])->first();

        $id = DB::table('cms_ab_tests')->insertGetId([
            'tenant_id' => $this->tenantId(),
            'site_id'   => $page->site_id,
            'page_id'   => $data['page_id'],
            'name'      => $data['name'],
            'goal'      => $data['goal'],
            'variants'  => $data['variants'],
            'status'    => 'running',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $test = DB::table('cms_ab_tests')->where('id', $id)->first();
        return response()->json($test, 201);
    }

    public function toggle(string $id)
    {
        $test = DB::table('cms_ab_tests')->where('id', $id)->where('tenant_id', $this->tenantId())->first();
        abort_if(!$test, 404);

        $newStatus = $test->status === 'running' ? 'paused' : 'running';
        DB::table('cms_ab_tests')->where('id', $id)->update(['status' => $newStatus, 'updated_at' => now()]);

        return response()->json(['status' => $newStatus]);
    }

    public function declareWinner(Request $request, string $id)
    {
        $test = DB::table('cms_ab_tests')->where('id', $id)->where('tenant_id', $this->tenantId())->first();
        abort_if(!$test, 404);

        $variantName = $request->input('variant_name');
        $variants    = json_decode($test->variants, true);

        foreach ($variants as &$v) {
            $v['winner'] = ($v['name'] === $variantName);
        }

        DB::table('cms_ab_tests')->where('id', $id)->update([
            'status'         => 'completed',
            'winner_variant' => $variantName,
            'variants'       => json_encode($variants),
            'updated_at'     => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
