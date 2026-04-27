<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\InventoryIssueLine;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryIssueController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;

        $query = $this->buildIssueQuery($request, $tenantId)
            ->latest('issue_date')
            ->latest('id');

        $issues = $query->paginate((int) $request->input('per_page', 25));
        $issues->through(fn (InventoryIssueLine $line) => $this->transformIssueLine($line));

        return response()->json([
            'data' => $issues,
            'meta' => $this->metaOptions($tenantId),
            'summary' => [
                'open' => InventoryIssueLine::query()
                    ->when($tenantId, fn ($q) => $q->whereHas('item', fn ($iq) => $iq->where('tenant_id', $tenantId)))
                    ->where('status', 'Open')
                    ->count(),
                'pending_return' => InventoryIssueLine::query()
                    ->when($tenantId, fn ($q) => $q->whereHas('item', fn ($iq) => $iq->where('tenant_id', $tenantId)))
                    ->where('returnable', true)
                    ->whereIn('status', ['Open', 'Partial_Returned'])
                    ->count(),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;

        $rows = $this->buildIssueQuery($request, $tenantId)
            ->latest('issue_date')
            ->latest('id')
            ->get()
            ->map(fn (InventoryIssueLine $line) => $this->transformIssueLine($line));

        $filename = 'inventory_issue_register_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Issue Date',
                'Item',
                'Issue Type',
                'Issued To Type',
                'Issued To',
                'Quantity',
                'Returned Qty',
                'Returnable',
                'Status',
                'Created By',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['id'],
                    $row['issue_date'],
                    $row['item']['name'] ?? null,
                    $row['issue_type'],
                    $row['issued_to_type'],
                    $row['issued_to_label'],
                    $row['quantity'],
                    $row['returned_qty'],
                    $row['returnable'] ? 'Yes' : 'No',
                    $row['status'],
                    $row['created_by'],
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
            'item_id' => 'required|exists:inventory_items,id',
            'issue_type' => 'required|in:Office_Consumption,Employee_Issue,Client_Delivery,Project_Use',
            'issued_to_type' => 'required|string|max:40',
            'issued_to_id' => 'required|integer|min:1',
            'quantity' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'returnable' => 'nullable|boolean',
        ]);

        $issuedToClass = $this->resolveIssuedToClass($validated['issued_to_type']);
        $issuedTo = $issuedToClass::find($validated['issued_to_id']);

        if (!$issuedTo) {
            throw ValidationException::withMessages([
                'issued_to_id' => 'Selected issue target does not exist.',
            ]);
        }

        $line = DB::transaction(function () use ($request, $validated, $issuedToClass) {
            $item = InventoryItem::query()->lockForUpdate()->findOrFail($validated['item_id']);

            if ($item->current_stock < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock. Available: {$item->current_stock}",
                ]);
            }

            $item->decrement('current_stock', $validated['quantity']);

            $line = InventoryIssueLine::create([
                'item_id' => $item->id,
                'issue_type' => $validated['issue_type'],
                'issued_to_type' => $issuedToClass,
                'issued_to_id' => $validated['issued_to_id'],
                'quantity' => $validated['quantity'],
                'issue_date' => $validated['issue_date'],
                'returnable' => (bool) ($validated['returnable'] ?? false),
                'status' => 'Open',
                'created_by' => optional($request->user())->id,
            ]);

            InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => 'Adjustment',
                'quantity' => $validated['quantity'],
                'requested_by' => optional($request->user())->id,
                'reason' => "Issued via line #{$line->id} ({$validated['issue_type']})",
            ]);

            return $line;
        });

        return response()->json([
            'message' => 'Inventory issue recorded.',
            'data' => $this->transformIssueLine($line->load(['item', 'createdBy', 'issuedTo'])),
        ], 201);
    }

    public function returnIssue(Request $request, InventoryIssueLine $issue)
    {
        $validated = $request->validate([
            'returned_qty' => 'required|numeric|min:0.01',
        ]);

        if (!$issue->returnable) {
            throw ValidationException::withMessages([
                'returned_qty' => 'This issue is not returnable.',
            ]);
        }

        $updatedIssue = DB::transaction(function () use ($request, $issue, $validated) {
            $issue = InventoryIssueLine::query()->lockForUpdate()->findOrFail($issue->id);
            $item = InventoryItem::query()->lockForUpdate()->findOrFail($issue->item_id);

            $pendingQty = (float) $issue->quantity - (float) $issue->returned_qty;

            if ($validated['returned_qty'] > $pendingQty) {
                throw ValidationException::withMessages([
                    'returned_qty' => "Return quantity cannot exceed pending quantity ({$pendingQty}).",
                ]);
            }

            $issue->returned_qty = (float) $issue->returned_qty + (float) $validated['returned_qty'];
            $issue->status = $issue->returned_qty >= $issue->quantity ? 'Closed' : 'Partial_Returned';
            $issue->save();

            $item->increment('current_stock', $validated['returned_qty']);

            InventoryTransaction::create([
                'item_id' => $item->id,
                'type' => 'Adjustment',
                'quantity' => $validated['returned_qty'],
                'requested_by' => optional($request->user())->id,
                'reason' => "Return against issue #{$issue->id}",
            ]);

            return $issue;
        });

        return response()->json([
            'message' => 'Return posted successfully.',
            'data' => $this->transformIssueLine($updatedIssue->load(['item', 'createdBy', 'issuedTo'])),
        ]);
    }

    protected function metaOptions(?int $tenantId): array
    {
        return [
            'items' => InventoryItem::query()
                ->select(['id', 'name', 'unit', 'current_stock'])
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get(),
            'users' => User::query()
                ->select(['id', 'name'])
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->orderBy('name')
                ->get(),
            'employees' => Employee::query()
                ->select(['id', 'first_name', 'last_name'])
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get()
                ->map(fn (Employee $employee) => [
                    'id' => $employee->id,
                    'name' => trim("{$employee->first_name} {$employee->last_name}"),
                ]),
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
            'issue_types' => ['Office_Consumption', 'Employee_Issue', 'Client_Delivery', 'Project_Use'],
            'issued_to_types' => ['User', 'Employee', 'Client', 'Project'],
        ];
    }

    protected function resolveIssuedToClass(string $type): string
    {
        return match ($type) {
            'User', User::class => User::class,
            'Employee', Employee::class => Employee::class,
            'Client', Client::class => Client::class,
            'Project', Project::class => Project::class,
            default => throw ValidationException::withMessages([
                'issued_to_type' => 'Invalid issue target type.',
            ]),
        };
    }

    protected function buildIssueQuery(Request $request, ?int $tenantId)
    {
        return InventoryIssueLine::query()
            ->with([
                'item:id,name,unit,current_stock,tenant_id',
                'createdBy:id,name',
                'issuedTo',
            ])
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->whereHas('item', fn ($itemQuery) => $itemQuery->where('tenant_id', $tenantId));
            })
            ->when($request->filled('item_id'), fn ($q) => $q->where('item_id', $request->integer('item_id')))
            ->when($request->filled('issue_type'), fn ($q) => $q->where('issue_type', $request->string('issue_type')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('returnable'), fn ($q) => $q->where('returnable', $request->boolean('returnable')))
            ->when($request->filled('issued_to_type'), fn ($q) => $q->where('issued_to_type', $this->resolveIssuedToClass($request->string('issued_to_type')->toString())))
            ->when($request->filled('issued_to_id'), fn ($q) => $q->where('issued_to_id', $request->integer('issued_to_id')))
            ->when($request->filled('from_date'), fn ($q) => $q->whereDate('issue_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($q) => $q->whereDate('issue_date', '<=', $request->date('to_date')));
    }

    protected function transformIssueLine(InventoryIssueLine $line): array
    {
        return [
            'id' => $line->id,
            'item_id' => $line->item_id,
            'issue_type' => $line->issue_type,
            'issued_to_type' => class_basename($line->issued_to_type),
            'issued_to_id' => $line->issued_to_id,
            'issued_to_label' => $this->modelLabel($line->issuedTo),
            'quantity' => (float) $line->quantity,
            'returned_qty' => (float) $line->returned_qty,
            'issue_date' => optional($line->issue_date)->toDateString(),
            'returnable' => (bool) $line->returnable,
            'status' => $line->status,
            'created_by' => $line->createdBy?->name,
            'item' => $line->item,
        ];
    }

    protected function modelLabel(?Model $model): string
    {
        if (!$model) {
            return 'Unknown';
        }

        if ($model instanceof Employee) {
            return trim("{$model->first_name} {$model->last_name}");
        }

        return (string) ($model->name ?? ('#' . $model->getKey()));
    }
}
