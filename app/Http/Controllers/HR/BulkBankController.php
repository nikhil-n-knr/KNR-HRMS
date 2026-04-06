<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeBankDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BulkBankController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'user', 'currentBankDetail'])
            ->where('status', 'active');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $employees = $query->get()
            ->filter(fn($emp) => $emp->user)
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->user->name,
                    'department' => $emp->department->name ?? '-',
                    // Bank Fields
                    'bank_name' => $emp->currentBankDetail?->bank_name ?? '',
                    'account_number' => $emp->currentBankDetail?->account_number ?? '',
                    'ifsc_code' => $emp->currentBankDetail?->ifsc_code ?? '',
                    'branch_name' => $emp->currentBankDetail?->branch_name ?? '',
                    'account_holder_name' => $emp->currentBankDetail?->account_holder_name ?? $emp->user->name,
                    'selected' => false
                ];
            })->values();

        return Inertia::render('HR/Payroll/Bulk/BankIndex', [
            'employees' => $employees,
            'departments' => \App\Models\Department::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:employees,id',
            'updates.*.bank_name' => 'required|string',
            'updates.*.account_number' => 'required|string',
            'updates.*.ifsc_code' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->updates as $update) {
                EmployeeBankDetail::updateOrCreate(
                    [
                        'employee_id' => $update['id'],
                        'is_primary' => true
                    ],
                    [
                        'bank_name' => $update['bank_name'],
                        'account_number' => $update['account_number'],
                        'ifsc_code' => $update['ifsc_code'],
                        'branch_name' => $update['branch_name'] ?? '',
                        'account_holder_name' => $update['account_holder_name'] ?? '',
                    ]
                );
            }
        });

        return back()->with('success', 'Bank details updated successfully.');
    }

    public function downloadSample()
    {
        $employees = Employee::with(['department', 'user', 'currentBankDetail'])
            ->where('status', 'active')
            ->get()
            ->filter(fn($e) => $e->user);

        $headers = [
            'employee_id',
            'employee_name',
            'bank_name',
            'account_number',
            'ifsc_code',
            'branch_name',
            'account_holder_name'
        ];

        return response()->streamDownload(function () use ($employees, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($employees as $emp) {
                fputcsv($handle, [
                    $emp->id,
                    $emp->user->name,
                    $emp->currentBankDetail?->bank_name ?? '',
                    $emp->currentBankDetail?->account_number ?? '',
                    $emp->currentBankDetail?->ifsc_code ?? '',
                    $emp->currentBankDetail?->branch_name ?? '',
                    $emp->currentBankDetail?->account_holder_name ?? $emp->user->name,
                ]);
            }
            fclose($handle);
        }, 'bulk_bank_template.csv');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fputcsv($handle, []); // Skip instructions if any, or just handle normally
        
        // Simple logic: read all rows
        $rows = [];
        $headers = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $rows[] = array_combine($headers, $data);
        }
        fclose($handle);

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (empty($row['employee_id'])) continue;

                EmployeeBankDetail::updateOrCreate(
                    [
                        'employee_id' => $row['employee_id'],
                        'is_primary' => true
                    ],
                    [
                        'bank_name' => $row['bank_name'],
                        'account_number' => $row['account_number'],
                        'ifsc_code' => $row['ifsc_code'],
                        'branch_name' => $row['branch_name'] ?? '',
                        'account_holder_name' => $row['account_holder_name'] ?? '',
                    ]
                );
            }
        });

        return back()->with('success', 'Bank details imported successfully.');
    }
}
