<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Employee;
use App\Services\HR\Employee360Service;
use Carbon\Carbon;

class Employee360Export implements WithMultipleSheets
{
    use Exportable;

    protected $employee;
    protected $startDate;
    protected $endDate;
    protected $service;

    public function __construct(Employee $employee, $startDate, $endDate, Employee360Service $service)
    {
        $this->employee = $employee;
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        $this->service = $service;
    }

    public function sheets(): array
    {
        $sheets = [];

        // 1. Overview Sheet (Metrics & AI Health)
        $metrics = $this->service->getDashboardMetrics($this->employee, $this->startDate, $this->endDate);
        $sheets[] = new Sheets\EmployeeOverviewSheet($this->employee, $metrics, $this->startDate, $this->endDate);

        // 2. Project Delivery & Task Utilization
        $projectData = $this->service->getProjectDeliveryData($this->employee, $this->startDate, $this->endDate);
        $sheets[] = new Sheets\EmployeeProjectDeliverySheet($projectData, $this->startDate, $this->endDate);

        // 3. Attendance & Timesheet
        $timesheetData = $this->service->getTimesheetData($this->employee, $this->startDate, $this->endDate);
        $sheets[] = new Sheets\EmployeeTimesheetAuditSheet($timesheetData);

        // 4. Quality & Bug Resolving
        $qualityData = $this->service->getQualityData($this->employee, $this->startDate, $this->endDate);
        $sheets[] = new Sheets\EmployeeQualityTrackerSheet($qualityData);

        // 5. Daily Attendance Grid (Detail)
        $attendanceGrid = $this->service->getDetailedAttendanceGrid($this->employee, $this->startDate, $this->endDate);
        $sheets[] = new Sheets\EmployeeAttendanceLogSheet($attendanceGrid);

        // 6. Request History & Audit
        $sheets[] = new Sheets\EmployeeRequestAuditSheet($this->employee, $this->startDate, $this->endDate);

        // 7. Learning & LMS
        if ($this->employee->user) {
            $learningData = $this->service->getLearningData($this->employee->user);
            if ($learningData->isNotEmpty()) {
                $sheets[] = new Sheets\EmployeeLearningSheet($learningData);
            }

            // 6. CRM & Sales Impact
            $crmData = $this->service->getCrmData($this->employee->user, $this->startDate, $this->endDate);
            if ($crmData->isNotEmpty()) {
                $sheets[] = new Sheets\EmployeeCrmImpactSheet($crmData);
            }
        }

        // 7. Finance & Compliance (Not implemented in deep DB logic here yet, but scaffolded)
        $sheets[] = new Sheets\EmployeeComplianceFinanceSheet($this->employee);

        return $sheets;
    }
}
