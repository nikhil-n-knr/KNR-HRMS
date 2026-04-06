<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\CrmCustomField;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|string|in:lead,contact,account,deal',
            'label' => 'required|string|max:255',
            'name' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'type' => 'required|string|in:text,number,select,date,boolean',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'order' => 'integer',
        ]);

        CrmCustomField::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$validated
        ]);

        return redirect()->back()->with('success', 'Custom field defined successfully.');
    }

    public function update(Request $request, CrmCustomField $customField)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|in:text,number,select,date,boolean',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'order' => 'integer',
        ]);

        $customField->update($validated);

        return redirect()->back()->with('success', 'Custom field updated successfully.');
    }

    public function destroy(CrmCustomField $customField)
    {
        $customField->delete();
        return redirect()->back()->with('success', 'Custom field deleted successfully.');
    }
}
