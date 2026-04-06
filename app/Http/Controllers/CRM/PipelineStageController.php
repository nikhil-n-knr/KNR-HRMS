<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\PipelineStage;
use Illuminate\Http\Request;

class PipelineStageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'order' => 'integer',
            'type' => 'required|string|in:open,won,lost',
            'win_probability' => 'integer|between:0,100',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'automation_rules' => 'nullable|array',
            'expected_duration_days' => 'nullable|integer',
        ]);

        if (!empty($validated['is_default'])) {
            PipelineStage::where('tenant_id', auth()->user()->tenant_id)->update(['is_default' => false]);
        }

        PipelineStage::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$validated
        ]);

        return redirect()->back()->with('success', 'Pipeline stage created successfully.');
    }

    public function update(Request $request, PipelineStage $pipelineStage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'order' => 'integer',
            'type' => 'required|string|in:open,won,lost',
            'win_probability' => 'integer|between:0,100',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'automation_rules' => 'nullable|array',
            'expected_duration_days' => 'nullable|integer',
        ]);

        if (!empty($validated['is_default'])) {
            PipelineStage::where('tenant_id', auth()->user()->tenant_id)
                ->where('id', '!=', $pipelineStage->id)
                ->update(['is_default' => false]);
        }

        $pipelineStage->update($validated);

        return redirect()->back()->with('success', 'Pipeline stage updated successfully.');
    }

    public function destroy(PipelineStage $pipelineStage)
    {
        $pipelineStage->delete();
        return redirect()->back()->with('success', 'Pipeline stage deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['stages' => 'required|array']);

        foreach ($request->stages as $item) {
            PipelineStage::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }
}
