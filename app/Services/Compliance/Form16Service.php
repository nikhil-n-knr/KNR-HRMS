<?php

namespace App\Services\Compliance;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\TaxDeclaration;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class Form16Service
{
    protected $taxComputationService;

    public function __construct(
        \App\Services\Tax\TaxComputationService $taxComputationService
    ) {
        $this->taxComputationService = $taxComputationService;
    }

    /**
     * Generate Form 16 Part B
     * Aggregates Income, Exemptions, Deductions, and Tax Calculation.
     */
    public function generatePartB(Employee $employee, int $fiscalYearStart)
    {
        $fiscalYearEnd = $fiscalYearStart + 1;
        $yearString = "$fiscalYearStart-$fiscalYearEnd";

        // Use the Centralized Tax Computation Logic
        $computedData = $this->taxComputationService->aggregateYearlyData($employee, $fiscalYearStart);

        $exemptions = [
            'HRA' => $computedData['hra_exemption'],
            'LTA' => 0, // Pending implementation
            'Standard Deduction' => $computedData['standard_deduction'], 
            'Professional Tax' => $computedData['professional_tax']
        ];

        $deductions = [
            '80C' => $computedData['section_80c'],
            '80D' => $computedData['section_80d']
        ];
        
        // Data Bundle
        $data = [
            'employer_name' => 'Acme Corp', // Fetch from Settings
            'employer_pan' => 'ABCDE1234F',
            'employer_tan' => 'ABCD12345E',
            'employee' => $employee,
            'year' => $yearString,
            'gross_salary' => $computedData['gross_salary'],
            'exemptions' => $exemptions,
            'deductions' => $deductions,
            'tax_paid' => $computedData['tds_paid'],
            'generated_at' => now()->format('d-M-Y')
        ];

        // 4. Generate PDF
        $pdf = Pdf::loadView('compliance.form16_part_b', $data);
        return $pdf;
    }

    /**
     * Merge Part A (External PDF) with Part B (Generated PDF)
     * 
     * @param string $partAPath Absolute path to Part A PDF
     * @param string $partBContent Raw binary content of Part B PDF
     * @return string Merged PDF binary content
     */
    public function mergePartAandPartB(string $partAPath, string $partBContent)
    {
        // 1. Initialize FPDI
        $pdf = new \setasign\Fpdi\Fpdi();

        // 2. Import Part A
        if (file_exists($partAPath)) {
            $pageCount = $pdf->setSourceFile($partAPath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($templateId);
            }
        }

        // 3. Import Part B (We need to save it to a temporary file first because FPDI usually reads from file or stream)
        // Alternatively, we can use StreamReader if available, but temp file is reliable.
        $tempPartB = tempnam(sys_get_temp_dir(), 'form16_partB_');
        file_put_contents($tempPartB, $partBContent);

        $pageCountB = $pdf->setSourceFile($tempPartB);
        for ($pageNo = 1; $pageNo <= $pageCountB; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
        }

        // Cleanup
        unlink($tempPartB);

        return $pdf->Output('S'); // Return as string
    }
}
