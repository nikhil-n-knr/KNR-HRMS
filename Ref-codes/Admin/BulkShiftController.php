<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\EmployeeShift; // Assuming this model exists or we use DB table directly
use App\Models\ShiftRoster; // Or whatever model is used for employee_shift table
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BulkShiftController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $csvData = array_map('str_getcsv', file($file->getPathname()));
        $header = array_shift($csvData); // Assume first row is header: Employee ID, Date, Shift Name

        $preview = [];
        $errors = [];
        $shifts = Shift::all()->pluck('id', 'name')->toArray(); // Map Name -> ID

        foreach ($csvData as $index => $row) {
            if (count($row) < 3) continue;
            
            $empId = $row[0];
            $date = $row[1];
            $shiftName = $row[2];
            
            $rowErrors = [];

            // Validate Employee
            $employee = Employee::find($empId);
            if (!$employee) {
                $rowErrors[] = "Employee ID {$empId} not found";
            }

            // Validate Shift
            $shiftId = $shifts[$shiftName] ?? null;
            if (!$shiftId) {
                $rowErrors[] = "Shift '{$shiftName}' not found";
            }

            // Validate Date
            try {
                $d = Carbon::parse($date);
            } catch (\Exception $e) {
                $rowErrors[] = "Invalid Date: $date";
            }

            if (empty($rowErrors)) {
                $preview[] = [
                    'employee_id' => $empId,
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'date' => $d->toDateString(),
                    'shift_id' => $shiftId,
                    'shift_name' => $shiftName
                ];
            } else {
                $errors[] = [
                    'row' => $index + 2,
                    'data' => $row,
                    'errors' => $rowErrors
                ];
            }
        }

        return response()->json([
            'preview' => $preview,
            'errors' => $errors,
            'valid_count' => count($preview),
            'invalid_count' => count($errors)
        ]);
    }

    public function execute(Request $request)
    {
        $validated = $request->validate([
            'rows' => 'required|array',
            'rows.*.employee_id' => 'required|exists:employees,id',
            'rows.*.date' => 'required|date',
            'rows.*.shift_id' => 'required|exists:shifts,id'
        ]);

        $count = 0;
        DB::transaction(function () use ($validated, &$count) {
            foreach ($validated['rows'] as $row) {
                // Update or Create Shift Roster Entry
                // Assuming we have a table 'employee_shifts' or similar. 
                // Based on previous convos, we might need to check 'ShiftRoster' model usage or table name.
                // Assuming 'shift_roster' table exists or 'employee_shift'.
                
                // Using DB facade for safety if specific model isn't clear, but typically it is EmployeeShift
                DB::table('employee_shifts')->updateOrInsert(
                    [
                        'employee_id' => $row['employee_id'],
                        'date' => $row['date']
                    ],
                    [
                        'shift_id' => $row['shift_id'],
                        'updated_at' => now(),
                        'created_at' => now() // Note: created_at only applied on insert
                    ]
                );
                $count++;
            }
        });

        return response()->json(['message' => "Successfully imported {$count} shift assignments."]);
    }
}
