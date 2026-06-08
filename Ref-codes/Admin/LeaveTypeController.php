<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Services\Infrastructure\LoggerService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        // Simply return JSON if requested via AJAX/Axios
        if ($request->wantsJson()) {
             // Continue to query...
        } else {
             // If accessed directly via browser, redirect to the Hub/Index tab
             return redirect()->route('admin.leave.index', ['tab' => 'types']);
        }

        $query = LeaveType::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $types = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        // API/Axios Request
        return response()->json($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:10', Rule::unique('leave_types')->whereNull('deleted_at')],
            'days_allowed_per_year' => 'required|numeric|min:0',
            'carry_forward_limit' => 'nullable|integer|min:0',
            'is_encashable' => 'boolean',
            'is_paid' => 'boolean',
            'requires_document' => 'boolean',
            'requires_approval' => 'boolean',
            'is_active' => 'nullable|boolean',
            'color' => 'nullable|string|max:7',
            'applicable_from' => 'nullable|date',
            'applicable_to' => 'nullable|date|after_or_equal:applicable_from',
        ]);

        // Default is_active to true if not provided in the request
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        
        $type = LeaveType::create($validated);
        $this->logger->log('leave_management', 'create', "Leave type created: {$type->name}");

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Leave type created successfully', 'data' => $type], 201);
        }

        return redirect()->back()->with('success', 'Leave type created successfully');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'code' => ['sometimes', 'string', 'max:10', Rule::unique('leave_types')->ignore($leaveType->id)->whereNull('deleted_at')],
            'days_allowed_per_year' => 'sometimes|numeric|min:0',
            'carry_forward_limit' => 'nullable|integer|min:0',
            'is_encashable' => 'boolean',
            'is_paid' => 'boolean',
            'requires_document' => 'boolean',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
            'color' => 'nullable|string|max:7',
            'applicable_from' => 'nullable|date',
            'applicable_to' => 'nullable|date|after_or_equal:applicable_from',
        ]);

        $leaveType->update($validated);
        $this->logger->log('leave_management', 'update', "Leave type updated: {$leaveType->name}");

        if ($request->wantsJson()) {
             return response()->json(['message' => 'Leave type updated successfully', 'data' => $leaveType]);
        }

        return redirect()->back()->with('success', 'Leave type updated successfully');
    }

    public function toggleActive(LeaveType $leaveType)
    {
        $leaveType->update(['is_active' => !$leaveType->is_active]);
        $status = $leaveType->is_active ? 'activated' : 'deactivated';
        $this->logger->log('leave_management', 'update', "Leave type {$status}: {$leaveType->name}");

        return response()->json([
            'message' => "Leave type {$status} successfully",
            'is_active' => $leaveType->is_active
        ]);
    }

    public function destroy(LeaveType $leaveType)
    {
        // Check for dependencies
        if ($leaveType->balances()->exists() || \App\Models\LeaveRequest::where('leave_type_id', $leaveType->id)->exists()) {
             if (request()->wantsJson()) {
                 return response()->json(['message' => 'Cannot delete: This leave type is actively in use by employees.'], 422);
             }
             return redirect()->back()->with('error', 'Cannot delete: This leave type is actively in use by employees.');
        }

        $leaveType->delete();
        $this->logger->log('leave_management', 'delete', "Leave type deleted: {$leaveType->name}");

        if (request()->wantsJson()) {
             return response()->json(['message' => 'Leave type deleted successfully']);
        }

        return redirect()->back()->with('success', 'Leave type deleted successfully');
    }
}
