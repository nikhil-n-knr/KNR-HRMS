<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientItemSupply;
use App\Models\InventoryIssueLine;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientItemSupplyController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;

        $query = $this->buildSupplyQuery($request, $tenantId)
            ->latest('supplied_on')
            ->latest('id');

        $supplies = $query->paginate((int) $request->input('per_page', 25));

        $summaryBase = ClientItemSupply::query()
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->whereHas('item', fn ($itemQuery) => $itemQuery->where('tenant_id', $tenantId));
            });

        return response()->json([
            'data' => $supplies,
            'meta' => [
                'items' => InventoryItem::query()
                    ->select(['id', 'name', 'unit', 'current_stock'])
                    ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                    ->orderBy('name')
                    ->get(),
                'clients' => Client::query()
                    ->select(['id', 'name'])
                    ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                    ->orderBy('name')
                    ->get(),
                'projects' => Project::query()
                    ->select(['id', 'name'])
                    ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                    ->orderBy('name')
                    ->get(),
            ],
            'summary' => [
                'total_qty' => (float) $summaryBase->sum('supplied_qty'),
                'total_value' => (float) $summaryBase->sum(DB::raw('COALESCE(unit_rate, 0) * supplied_qty')),
                'records' => (int) $summaryBase->count(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;

        $rows = $this->buildSupplyQuery($request, $tenantId)
            ->latest('supplied_on')
            ->latest('id')
            ->get();

        $filename = 'client_supply_register_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Supply Date',
                'Client',
                'Item',
                'Project',
                'Qty',
                'Unit',
                'Unit Rate',
                'Total Value',
                'Notes',
            ]);

            foreach ($rows as $row) {
                $rate = (float) ($row->unit_rate ?? 0);
                $qty = (float) $row->supplied_qty;

                fputcsv($handle, [
                    $row->id,
                    optional($row->supplied_on)->toDateString(),
                    $row->client?->name,
                    $row->item?->name,
                    $row->project?->name,
                    $qty,
                    $row->item?->unit,
                    $row->unit_rate,
                    round($rate * $qty, 2),
                    $row->notes,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'item_id' => 'required|exists:inventory_items,id',
            'supplied_qty' => 'required|numeric|min:0.01',
            'unit_rate' => 'nullable|numeric|min:0',
            'supplied_on' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $supply = DB::transaction(function () use ($request, $validated) {
            $item = InventoryItem::query()->lockForUpdate()->findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['supplied_qty']) {
                throw ValidationException::withMessages([
                    'supplied_qty' => "Insufficient stock. Available: {$item->current_stock}",
                ]);
            }

            $item->decrement('current_stock', $validated['supplied_qty']);

            $supply = ClientItemSupply::create($validated);

            InventoryIssueLine::create([
                'item_id' => $item->id,
                'issue_type' => 'Client_Delivery',
                'issued_to_type' => Client::class,
                'issued_to_id' => $validated['client_id'],
                'quantity' => $validated['supplied_qty'],
                'issue_date' => $validated['supplied_on'],
                'returnable' => false,
                'status' => 'Closed',
                'created_by' => optional($request->user())->id,
            ]);

            InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => 'Adjustment',
                'quantity' => $validated['supplied_qty'],
                'requested_by' => optional($request->user())->id,
                'reason' => "Client supply #{$supply->id} created",
            ]);

            return $supply;
        });

        return response()->json([
            'message' => 'Client supply recorded.',
            'data' => $supply->load(['client:id,name', 'item:id,name,unit,current_stock', 'project:id,name']),
        ], 201);
    }

    public function update(Request $request, ClientItemSupply $supply)
    {
        $validated = $request->validate([
            'supplied_qty' => 'required|numeric|min:0.01',
            'unit_rate' => 'nullable|numeric|min:0',
            'supplied_on' => 'required|date',
            'project_id' => 'nullable|exists:projects,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $updated = DB::transaction(function () use ($request, $supply, $validated) {
            $lockedSupply = ClientItemSupply::query()->lockForUpdate()->findOrFail($supply->id);
            $item = InventoryItem::query()->lockForUpdate()->findOrFail($lockedSupply->item_id);

            $delta = (float) $validated['supplied_qty'] - (float) $lockedSupply->supplied_qty;

            if ($delta > 0 && $item->current_stock < $delta) {
                throw ValidationException::withMessages([
                    'supplied_qty' => "Insufficient stock for increase. Available: {$item->current_stock}",
                ]);
            }

            if ($delta > 0) {
                $item->decrement('current_stock', $delta);
            } elseif ($delta < 0) {
                $item->increment('current_stock', abs($delta));
            }

            $lockedSupply->update($validated);

            InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => 'Adjustment',
                'quantity' => abs($delta),
                'requested_by' => optional($request->user())->id,
                'reason' => "Client supply #{$lockedSupply->id} updated",
            ]);

            return $lockedSupply;
        });

        return response()->json([
            'message' => 'Client supply updated.',
            'data' => $updated->load(['client:id,name', 'item:id,name,unit,current_stock', 'project:id,name']),
        ]);
    }

    public function destroy(Request $request, ClientItemSupply $supply)
    {
        DB::transaction(function () use ($request, $supply) {
            $lockedSupply = ClientItemSupply::query()->lockForUpdate()->findOrFail($supply->id);
            $item = InventoryItem::query()->lockForUpdate()->findOrFail($lockedSupply->item_id);

            $item->increment('current_stock', $lockedSupply->supplied_qty);

            InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => 'Adjustment',
                'quantity' => $lockedSupply->supplied_qty,
                'requested_by' => optional($request->user())->id,
                'reason' => "Client supply #{$lockedSupply->id} deleted and stock reversed",
            ]);

            $lockedSupply->delete();
        });

        return response()->json(['message' => 'Client supply removed and stock restored.']);
    }

    protected function buildSupplyQuery(Request $request, ?int $tenantId)
    {
        return ClientItemSupply::query()
            ->with([
                'client:id,name',
                'item:id,name,unit,current_stock,tenant_id',
                'project:id,name',
            ])
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->whereHas('item', fn ($itemQuery) => $itemQuery->where('tenant_id', $tenantId));
            })
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->integer('client_id')))
            ->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $request->integer('item_id')))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->integer('project_id')))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('supplied_on', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('supplied_on', '<=', $request->date('to_date')));
    }
}
