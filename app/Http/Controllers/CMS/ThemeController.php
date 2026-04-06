<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CMS\Theme;
use Illuminate\Support\Facades\DB;

class ThemeController extends Controller
{
    protected function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index()
    {
        $themes = Theme::where('tenant_id', $this->tenantId())->get();
        return response()->json($themes);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'config'    => 'nullable|array',
        ]);
        $data['tenant_id'] = $this->tenantId();
        $data['config'] ??= [
            'colors'     => ['primary' => '#10b981', 'secondary' => '#6366f1', 'accent' => '#f59e0b'],
            'typography' => ['font_heading' => 'Inter', 'font_body' => 'Inter', 'scale' => 1.25],
        ];

        $theme = Theme::create($data);
        return response()->json($theme, 201);
    }

    public function show(string $id)
    {
        return response()->json(Theme::where('tenant_id', $this->tenantId())->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $theme = Theme::where('tenant_id', $this->tenantId())->findOrFail($id);
        $theme->update($request->validate(['name' => 'nullable|string', 'config' => 'nullable|array']));
        return response()->json($theme);
    }

    public function destroy(string $id)
    {
        Theme::where('tenant_id', $this->tenantId())->findOrFail($id)->delete();
        return response()->json(['message' => 'Theme deleted.']);
    }

    /**
     * Set this theme as active on the site.
     */
    public function activate(Request $request, string $id)
    {
        $theme  = Theme::where('tenant_id', $this->tenantId())->findOrFail($id);
        $siteId = $request->get('site_id');

        if ($siteId) {
            DB::table('cms_sites')->where('id', $siteId)->update(['theme_id' => $theme->id]);
        }

        return response()->json(['activated' => true, 'theme_id' => $theme->id]);
    }

    /**
     * Compile the theme into CSS variables (stub — extend with real compiler).
     */
    public function compile(Request $request, string $id)
    {
        $theme = Theme::where('tenant_id', $this->tenantId())->findOrFail($id);
        $config = $theme->config ?? [];

        $colors     = $config['colors'] ?? [];
        $typography = $config['typography'] ?? [];

        $css = ":root {\n";
        foreach ($colors as $key => $value) {
            $css .= "  --color-{$key}: {$value};\n";
        }
        if (!empty($typography['font_heading'])) {
            $css .= "  --font-heading: '{$typography['font_heading']}', sans-serif;\n";
        }
        if (!empty($typography['font_body'])) {
            $css .= "  --font-body: '{$typography['font_body']}', sans-serif;\n";
        }
        if (!empty($typography['scale'])) {
            $css .= "  --type-scale: {$typography['scale']};\n";
        }
        $css .= "}\n";

        // In production: write to public/sites/{site_id}/theme.css
        return response()->json(['css' => $css, 'compiled' => true]);
    }
}
