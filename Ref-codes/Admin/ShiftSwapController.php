<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ShiftSwap;
use Illuminate\Support\Facades\Response;

class ShiftSwapController extends Controller
{
    public function index(Request $request)
    {
        $query = ShiftSwap::with(['requester', 'recipient', 'shiftFrom', 'shiftTo']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('requester', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy('date', 'desc');

        $swaps = $query->paginate(15);

        if ($request->wantsJson()) {
                return response()->json([
                'swaps' => $swaps,
                'filters' => $request->only(['search', 'status', 'department_id', 'location_id']),
                'departments' => \App\Models\Department::select('id', 'name')->get(),
                'swaps' => $swaps,
                'filters' => $request->only(['search', 'status', 'department_id', 'location_id']),
                'departments' => \App\Models\Department::select('id', 'name')->get(),
                'locations' => \App\Models\Location::select('id', 'name')->get(),
                'shifts' => \App\Models\Shift::select('id', 'name')->get(),
                'employees' => \App\Models\Employee::select('id', 'first_name', 'last_name', 'employee_code')
                    ->with(['rotations' => function($q) {
                        $q->where('start_date', '<=', now())->orderBy('start_date', 'desc')->limit(1);
                    }])
                    ->orderBy('first_name')
                    ->get()
                    ->map(function($employee) {
                        // Optimally, fetch default shift once outside, but for now this works or we can optimize if slow.
                        // Actually, I can't look up default shift *inside* value without cache or query.
                        // I'll leave it simple: if rotation exists, use it. If not, null (frontend defaults or user selects).
                        // Or better: Just include rotation info. Frontend has Shifts list, it knows which is default?
                        // No. I'll pass the ID.
                        $employee->current_shift_id = $employee->rotations->first()?->shift_id;
                        unset($employee->rotations);
                        return $employee;
                    })
            ]);
        }

        // If accessed directly via browser, redirect to Hub
        return to_route('admin.attendance.hub', ['tab' => 'swaps']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
             'requester_id' => 'required|exists:employees,id',
             'recipient_id' => 'required|exists:employees,id|different:requester_id',
             'shift_id_from' => 'required|exists:shifts,id',
             'shift_id_to' => 'required|exists:shifts,id',
             'date' => 'required|date',
        ]);

        ShiftSwap::create([
            'requester_id' => $validated['requester_id'],
            'recipient_id' => $validated['recipient_id'],
            'shift_id_from' => $validated['shift_id_from'],
            'shift_id_to' => $validated['shift_id_to'],
            'shift_id_to' => $validated['shift_id_to'],
            'date' => $validated['date'],
            'status' => 'Pending', // Schema allows: Pending, Accepted, Approved, Rejected
            // 'approved_by' => null
        ]);

        return redirect()->back()->with('success', 'Shift swap created and approved.');
    }

    public function export(Request $request)
    {
         $query = ShiftSwap::with(['requester', 'recipient', 'shiftFrom', 'shiftTo']);
         
         $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="shift_swaps.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Requested Date', 'Requester', 'Recipient', 'Current Shift', 'Requested Shift', 'Status']);

            $query->chunk(100, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->date,
                        $row->requester->first_name . ' ' . $row->requester->last_name,
                        $row->recipient ? ($row->recipient->first_name . ' ' . $row->recipient->last_name) : 'N/A',
                        $row->shiftFrom->name,
                        $row->shiftTo->name,
                        $row->status
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
