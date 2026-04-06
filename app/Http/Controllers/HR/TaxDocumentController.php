<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Employee;
use App\Models\EmployeeTaxRegime;
use App\Models\TaxDeclaration;
use App\Models\EmployeeHraDeclaration;

class TaxDocumentController extends Controller
{
    /**
     * Generate Form 12BB (Statement of Claims)
     */
    public function downloadForm12BB()
    {
        $employee = auth()->user()->employee;
        if (!$employee) abort(404, 'Employee not found');

        $currentMonth = now()->month;
        $year = now()->year;
        $fiscalYear = ($currentMonth > 3) ? "$year-".($year+1) : ($year-1)."-$year";
        
        // 1. Fetch Data
        $declarations = TaxDeclaration::where('employee_id', $employee->id)
            ->where('fiscal_year', $fiscalYear)
            ->with(['section'])
            ->get();
            
        $hra = EmployeeHraDeclaration::where('employee_id', $employee->id)
            ->where('fiscal_year', $fiscalYear)
            ->first();
            
        // Group Deductions
        $deductions = [
            '80C' => $declarations->where('section.section_code', '80C')->sum('declared_amount'),
            '80D' => $declarations->where('section.section_code', '80D')->sum('declared_amount'),
            '80E' => $declarations->where('section.section_code', '80E')->sum('declared_amount'),
            '80G' => $declarations->where('section.section_code', '80G')->sum('declared_amount'),
            '24b' => $declarations->where('section.section_code', '24(b)')->sum('declared_amount'), // Home Loan
            'other' => $declarations->filter(function($d) {
                return !in_array($d->section->section_code, ['80C', '80D', '80E', '80G', '24(b)', '10(13A)']);
            })->sum('declared_amount')
        ];
        
        $data = [
            'employee' => $employee,
            'fiscalYear' => $fiscalYear,
            'hra' => $hra,
            'deductions' => $deductions,
            'declarations' => $declarations,
            'date' => now()->format('d-M-Y')
        ];
        
        $pdf = Pdf::loadView('pdf.tax.form12bb', $data);
        return $pdf->download("Form12BB_{$employee->employee_code}_{$fiscalYear}.pdf");
    }

    /**
     * Generate Form 16 Part B (Salary Certificate)
     */
    public function downloadForm16(\App\Services\Compliance\Form16Service $service)
    {
        $employee = auth()->user()->employee;
        if (!$employee) abort(404, 'Employee not found');
        
        // Determine Year: If current month > 3 (Apr onwards), we are in FY X-(X+1). Form 16 usually for (X-1)-X.
        // E.g., June 2026 -> FY 26-27. Form 16 for 25-26.
        // If Jan 2026 -> FY 25-26. Form 16 for 24-25.
        // So always (Current Year - 1) if month > 3, else (Current Year - 1). 
        
        $currentMonth = now()->month;
        $baseYear = ($currentMonth >= 4) ? now()->year : now()->year - 1;
        $targetFiscalYearStart = $baseYear - 1;
        
        // Allow Override
        if (request('year')) {
            $targetFiscalYearStart = (int) request('year');
        }

        $pdf = $service->generatePartB($employee, $targetFiscalYearStart);
        
        // Check for Part A
        // Storage Path: storage/app/compliance/form16_partA/{year}/{emp_code}.pdf
        // We assume Admin has uploaded it here.
        $partAPath = storage_path("app/compliance/form16_partA/{$targetFiscalYearStart}/{$employee->employee_code}.pdf");
        
        if (file_exists($partAPath)) {
            $mergedContent = $service->mergePartAandPartB($partAPath, $pdf->output());
            
            return response($mergedContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"Form16_Merged_{$employee->employee_code}_{$targetFiscalYearStart}-" . ($targetFiscalYearStart+1) . ".pdf\"");
        }

        return $pdf->download("Form16_PartB_{$employee->employee_code}_{$targetFiscalYearStart}-" . ($targetFiscalYearStart+1) . ".pdf");
    }
}

