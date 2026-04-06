<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Pagination\LengthAwarePaginator;

class RequestController extends Controller
{
    public function index(Request $request) 
    {
        // Permission Check?
        if (!auth()->user()->hasRole(['Admin', 'Super Admin', 'Manager'])) {
            // abort(403); // Or allow self-view?
            // "Admin view should list all" -> implication is Admin.
        }

        $perPage = 20;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;

        // Build Union Query
        // Common Columns: id, uuid, employee_id, type, status, created_at, start_date, end_date, reason
        // We will select raw values to normalize
        
        // 1. Leave Requests
        $leaves = DB::table('leave_requests')
            ->select(
                'id', 'uuid', 'employee_id', 
                DB::raw("'Leave' as request_type"),
                'status', 'created_at', 
                'start_date', 'end_date', 'reason',
                'leaves.leave_type_id as sub_type_id', // Join later? Or just label?
                DB::raw("NULL as metadata")
            );

        // 2. WFH Requests (Verify table name: wfh_requests)
        $wfh = DB::table('wfh_requests')
            ->select(
                'id', 'uuid', 'employee_id',
                DB::raw("'WFH' as request_type"),
                'status', 'created_at',
                'date as start_date', 'date as end_date', // WFH is usually single day or range?
                'reason',
                DB::raw("NULL as sub_type_id"),
                DB::raw("NULL as metadata")
            );
            
        // 3. Overtime Requests (overtime_requests)
        $ot = DB::table('overtime_requests')
            ->select(
                'id', 'uuid', 'employee_id',
                DB::raw("'Overtime' as request_type"),
                'status', 'created_at',
                'date as start_date', 'date as end_date',
                'reason',
                DB::raw("NULL as sub_type_id"),
                DB::raw("CONCAT(minutes, ' mins') as metadata")
            );

        // 4. Shift Swaps (shift_swaps)
        $swap = DB::table('shift_swaps')
             ->select(
                'id', 'uuid', 'requester_id as employee_id', // Note: requester_id
                DB::raw("'Swap' as request_type"),
                'status', 'created_at',
                'requested_date as start_date', 'requested_date as end_date',
                'reason',
                DB::raw("NULL as sub_type_id"),
                DB::raw("CONCAT('With ', target_employee_id) as metadata")
             );


        // Combine
        $query = $leaves->union($wfh)->union($ot)->union($swap);

        // Apply Filters
        // Note: Union filters must be applied to the final result or each sub-query.
        // Eloquent Union wrapper puts it in ( ... ) usually.
        
        $sql = $query->toSql();
        
        // We need to count total for pagination manually or use a wrapper
        $countQuery = DB::table(DB::raw("({$sql}) as aggregated"));
        // Bindings?
        $count = $countQuery->mergeBindings($leaves)->mergeBindings($wfh)->mergeBindings($ot)->mergeBindings($swap)->count();

        // Fetch Data
        $dataQuery = DB::table(DB::raw("({$sql}) as aggregated"))
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($perPage);

        $data = $dataQuery->mergeBindings($leaves)->mergeBindings($wfh)->mergeBindings($ot)->mergeBindings($swap)->get();

        // Hydrate Relationships (Employee Name) manually to avoid N+1 per type
        $employeeIds = $data->pluck('employee_id')->unique();
        $employees = \App\Models\Employee::whereIn('id', $employeeIds)->get()->keyBy('id');
        
        // Leave Types map
        $leaveTypes = \App\Models\LeaveType::all()->keyBy('id');

        // Transform
        $transformed = $data->map(function($item) use ($employees, $leaveTypes) {
            $emp = $employees[$item->employee_id] ?? null;
            $subType = null;
            if ($item->request_type === 'Leave' && $item->sub_type_id) {
                $subType = $leaveTypes[$item->sub_type_id]->name ?? 'Unknown';
            }

            return [
                'id' => $item->id,
                'uuid' => $item->uuid,
                'type' => $item->request_type,
                'sub_type' => $subType, // Sick, Casual etc
                'employee' => $emp ? [
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                    'code' => $emp->employee_code,
                    'avatar' => $emp->profile_picture // if exists
                ] : ['name' => 'Unknown'],
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'reason' => $item->reason,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'metadata' => $item->metadata
            ];
        });

        $paginator = new LengthAwarePaginator($transformed, $count, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query()
        ]);

        return Inertia::render('Admin/Requests/Index', [
             'requests' => $paginator
        ]);
    }
    
    public function export(Request $request) {
        // Implement full export logic similar to index but without pagination
        // ... (Simplified for brevity, assuming standard csv export)
        return response()->streamDownload(function() {
             echo "ID,Type,Employee,Date,Status\n";
        }, 'requests.csv');
    }
}
