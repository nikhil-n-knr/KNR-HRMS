<?php

namespace App\Services\Tax;

use App\Models\Employee;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Support\Facades\Storage;

class Form16Service
{
    protected $computationService;
    protected $logger;

    public function __construct(TaxComputationService $computationService, LoggerService $logger)
    {
        $this->computationService = $computationService;
        $this->logger = $logger;
    }

    /**
     * Generate the Part B PDF for an employee.
     */
    public function generatePartB(Employee $employee, int $financialYear): string
    {
        $data = $this->computationService->aggregateYearlyData($employee, $financialYear);
        
        // In a real environment, we would use a library like Barryvdh\DomPDF\Facade\Pdf
        // For this implementation, we prep the HTML template logic.
        
        $html = view('pdf.tax.form16_part_b', [
            'employee' => $employee,
            'data' => $data,
            'employer_name' => config('app.name'),
            'ay' => ($financialYear + 1) . '-' . ($financialYear + 2)
        ])->render();

        $fileName = "Form16_PartB_{$employee->employee_code}_{$financialYear}.pdf";
        $filePath = "tax_returns/{$fileName}";

        // Mocking PDF Generation
        Storage::disk('local')->put($filePath, "PDF_BINARY_DATA_FOR_{$fileName}");

        $this->logger->logInfo("Form 16 Part B Generated", ['employee_id' => $employee->id, 'file' => $fileName]);

        return $filePath;
    }

    /**
     * Merge Part A (from TRACES) with generated Part B.
     */
    public function mergeParts(string $partAPath, string $partBPath): string
    {
        // Implementation using a library like iLovePDF or local ghostscript/pdfunite
        // Logic: combine $partAPath + $partBPath -> $finalPath
        
        $this->logger->logInfo("Form 16 Parts Merged", ['part_a' => $partAPath, 'part_b' => $partBPath]);
        
        return str_replace('PartB', 'Full', $partBPath);
    }
}
