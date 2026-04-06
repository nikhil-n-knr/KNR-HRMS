<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\MarketingTemplate;
use Illuminate\Http\Request;

class MarketingTemplateController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $templates = MarketingTemplate::where('tenant_id', $tenantId)
            ->latest()
            ->get();
        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
        ]);

        $template = MarketingTemplate::create([
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            ...$request->all()
        ]);

        return response()->json($template, 201);
    }

    public function show(MarketingTemplate $marketingTemplate)
    {
        return response()->json($marketingTemplate);
    }

    public function update(Request $request, MarketingTemplate $marketingTemplate)
    {
        // Add authorization check $this->authorize('update', $marketingTemplate);

        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $marketingTemplate->update($request->all());

        return response()->json($marketingTemplate);
    }

    public function destroy(MarketingTemplate $marketingTemplate)
    {
         // Add authorization check
        $marketingTemplate->delete();
        return response()->json(['message' => 'Template deleted']);
    }
}
