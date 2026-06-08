<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetCategory;
use App\Models\AssetAttributeDefinition;
use App\Models\BusinessRule;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ConfigHubController extends Controller
{
    public function index(Request $request)
    {
        $settings = DB::table('system_settings')
            ->pluck('value', 'key')
            ->toArray();

        return Inertia::render('Admin/Configs/Index', [
            'tab'                 => $request->query('tab', 'attributes'),
            'expanded_category_id' => $request->query('category_id'),
            'categories'          => AssetCategory::with('attributeDefinitions')->withCount('assets')->get(),
            'rules'               => BusinessRule::latest()->get(),
            'settings'            => $settings,
        ]);
    }

    public function storeAttribute(Request $request)
    {
        $validated = $request->validate([
            'asset_category_id' => 'required|exists:asset_categories,id',
            'name'              => 'required|string|max:50',
            'field_type'        => 'required|in:text,number,boolean,date,select',
            'options'           => 'nullable|array',
            'is_required'       => 'boolean'
        ]);

        AssetAttributeDefinition::create($validated);

        return back()->with('success', 'Attribute Slot Created');
    }

    public function updateAttribute(Request $request, AssetAttributeDefinition $attribute)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:50',
            'field_type'  => 'required|in:text,number,boolean,date,select',
            'options'     => 'nullable|array',
            'is_required' => 'boolean'
        ]);

        $attribute->update($validated);

        return back()->with('success', 'Attribute Slot Updated');
    }

    public function destroyAttribute(AssetAttributeDefinition $attribute)
    {
        $attribute->delete();
        return back()->with('success', 'Attribute Slot Deleted');
    }

    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'module'        => 'required|string',
            'trigger_event' => 'required|string',
            'conditions'    => 'required|array',
            'actions'       => 'required|array',
            'is_active'     => 'boolean'
        ]);

        BusinessRule::create($validated);

        return back()->with('success', 'Business Rule Injected Successfully');
    }

    public function updateRule(Request $request, BusinessRule $rule)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'module'        => 'required|string',
            'trigger_event' => 'required|string',
            'conditions'    => 'required|array',
            'actions'       => 'required|array',
            'is_active'     => 'boolean'
        ]);

        $rule->update($validated);

        return back()->with('success', 'Business Rule Updated');
    }

    public function destroyRule(BusinessRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Business Rule Deleted');
    }

    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'currency_code'   => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'currency_name'   => 'required|string|max:50',
            'date_format'     => 'required|string',
            'timezone'        => 'required|string',
        ]);

        $now = now();
        foreach ($validated as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
