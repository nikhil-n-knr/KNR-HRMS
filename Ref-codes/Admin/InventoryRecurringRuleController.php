<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\LocationNode;
use App\Models\RecurringConsumptionRule;
use Illuminate\Http\Request;

class InventoryRecurringRuleController extends Controller
{
    public function index(Request $request)
    {
        $query = RecurringConsumptionRule::query()->with('item');

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->integer('item_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $rules = $query->latest()->paginate(20);

        $items = InventoryItem::query()
            ->select(['id', 'name', 'sku', 'unit', 'current_stock'])
            ->orderBy('name')
            ->limit(200)
            ->get();

        $locations = LocationNode::query()
            ->select(['id', 'parent_id', 'name', 'node_type'])
            ->orderBy('node_type')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $rules,
            'meta' => [
                'items' => $items,
                'locations' => $locations,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'scope_type' => 'required|string|max:120',
            'scope_id' => 'required|integer|min:1',
            'frequency' => 'required|in:Weekly,Monthly',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'expected_qty' => 'required|numeric|min:0.01',
            'auto_create_request' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $rule = RecurringConsumptionRule::create([
            'item_id' => $validated['item_id'],
            'scope_type' => $validated['scope_type'],
            'scope_id' => $validated['scope_id'],
            'frequency' => $validated['frequency'],
            'day_of_week' => $validated['day_of_week'] ?? null,
            'day_of_month' => $validated['day_of_month'] ?? null,
            'expected_qty' => $validated['expected_qty'],
            'auto_create_request' => $validated['auto_create_request'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json(['message' => 'Recurring rule created.', 'data' => $rule], 201);
    }

    public function update(Request $request, RecurringConsumptionRule $rule)
    {
        $validated = $request->validate([
            'frequency' => 'required|in:Weekly,Monthly',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'expected_qty' => 'required|numeric|min:0.01',
            'auto_create_request' => 'required|boolean',
            'is_active' => 'required|boolean',
        ]);

        $rule->update($validated);

        return response()->json(['message' => 'Recurring rule updated.', 'data' => $rule->fresh()]);
    }

    public function destroy(RecurringConsumptionRule $rule)
    {
        $rule->delete();

        return response()->json(['message' => 'Recurring rule deleted.']);
    }
}
