<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SegmentController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $siteId = $request->get('site_id');
        $segments = DB::table('cms_segments')
            ->where('tenant_id', $this->tenantId())
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->whereNull('deleted_at')
            ->get()
            ->map(function($s) {
                $s->rules = json_decode($s->rules ?? '[]', true);
                $s->tags = []; // Mock tags for now
                // In a real system, we would calculate count here or via a job
                $s->count = $s->member_count; 
                return $s;
            });

        return response()->json($segments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logic' => 'required|in:ALL,ANY',
            'rules' => 'nullable|array',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        $data['tenant_id'] = $this->tenantId();
        $data['rules'] = json_encode($data['rules'] ?? []);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $id = DB::table('cms_segments')->insertGetId($data);

        return response()->json(DB::table('cms_segments')->find($id), 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'logic' => 'sometimes|in:ALL,ANY',
            'rules' => 'nullable|array',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        if (isset($data['rules'])) {
            $data['rules'] = json_encode($data['rules']);
        }
        $data['updated_at'] = now();

        DB::table('cms_segments')
            ->where('tenant_id', $this->tenantId())
            ->where('id', $id)
            ->update($data);

        return response()->json(DB::table('cms_segments')->find($id));
    }

    public function destroy($id)
    {
        DB::table('cms_segments')
            ->where('tenant_id', $this->tenantId())
            ->where('id', $id)
            ->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Segment deleted']);
    }
}
