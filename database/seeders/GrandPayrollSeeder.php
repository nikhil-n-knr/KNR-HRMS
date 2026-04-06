<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Location;
use App\Models\EmployeeSalary;
use App\Models\Payroll;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\TaxDeclaration;
use App\Models\InvestmentProof;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GrandPayrollSeeder extends Seeder
{
    private $employees = [];

    public function run()
    {
        // Cleanup Transactional Tables to prevent duplicates on re-seed
        // Move outside transaction as Truncate/FK checks cause implicit commit
        $this->command->info('0. Cleaning Transactional Data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('expenses')->truncate();
        DB::table('loans')->truncate();
        DB::table('loan_repayments')->truncate();
        DB::table('payrolls')->truncate();
        DB::table('tax_declarations')->truncate();
        DB::table('investment_proofs')->truncate();
        // Performance
        DB::table('goal_ratings')->truncate();
        DB::table('appraisals')->truncate();
        DB::table('goals')->truncate();
        DB::table('appraisal_cycles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::transaction(function () {
            $this->command->info('1. Seeding Configurations...');
            $this->seedConfigs();

            $this->command->info('2. Creating Personas...');
            $this->seedPersonas();

            $this->command->info('3. Simulating History (Oct - Dec)...');
            $this->seedHistory();

            $this->command->info('4. Setting Current State (Jan)...');
            $this->seedCurrent();
        });
    }

    private function seedConfigs()
    {
        // Expense Settings
        // Correct columns: requires_bill_proof, policy_settings (JSON)
        ExpenseCategory::firstOrCreate(['name' => 'Local Travel'], [
            'requires_bill_proof' => false,
            'policy_settings' => json_encode(['limit' => 2000]),
            'is_active' => true
        ]);
        
        ExpenseCategory::firstOrCreate(['name' => 'Client Dinner'], [
            'requires_bill_proof' => true,
            'policy_settings' => json_encode(['limit' => 5000]),
            'is_active' => true
        ]);
        
        ExpenseCategory::firstOrCreate(['name' => 'Internet'], [
            'requires_bill_proof' => true,
            'policy_settings' => json_encode(['limit' => 1200]),
            'is_active' => true
        ]);
        
        // Loan Products
        // Correct columns: max_amount_limit
        LoanProduct::firstOrCreate(['name' => 'Personal Loan'], ['interest_rate' => 10.0, 'max_amount_limit' => 500000]);
        LoanProduct::firstOrCreate(['name' => 'Salary Advance'], ['interest_rate' => 0.0, 'max_amount_limit' => 50000]);
        LoanProduct::firstOrCreate(['name' => 'Home Deposit'], ['interest_rate' => 0.0, 'max_amount_limit' => 200000]);

        // Default Structure
        \App\Models\SalaryStructure::firstOrCreate(['id' => 1], [
             'name' => 'Standard Structure',
             'description' => 'Default'
        ]);
    }

    private function seedPersonas()
    {
        // Departments
        $eng = Department::firstOrCreate(['name' => 'Engineering'], ['tenant_id' => 1]);
        $sales = Department::firstOrCreate(['name' => 'Sales'], ['tenant_id' => 1]);
        $hr = Department::firstOrCreate(['name' => 'HR'], ['tenant_id' => 1]);

        // 1. John (Senior, Stable)
        $john = $this->createEmployee('John', 'Doe', 'EMP001', $eng, 'Senior Engineer', 1800000);
        $this->employees['john'] = $john;

        // 2. Sarah (Sales, Claims)
        $sarah = $this->createEmployee('Sarah', 'Smith', 'EMP002', $sales, 'Account Executive', 1200000);
        $this->employees['sarah'] = $sarah;

        // 3. Mike (Junior, Loans)
        $mike = $this->createEmployee('Mike', 'Jones', 'EMP003', $eng, 'Junior Engineer', 600000);
        $this->employees['mike'] = $mike;
        
        // 4. Emma (Exit)
        $emma = $this->createEmployee('Emma', 'Wilson', 'EMP004', $hr, 'HR Executive', 500000);
        $this->employees['emma'] = $emma;
        
        // 5. Manager (Approver)
        $boss = $this->createEmployee('Charlie', 'Boss', 'MGR001', $eng, 'Engineering Manager', 3000000);
        $this->employees['boss'] = $boss;

        // Assign Reporting Manager to Boss's User ID (System)
        // Ensure boss has a user_id
        if ($boss->user_id) {
             Employee::whereIn('id', [
                 $this->employees['john']->id, 
                 $this->employees['sarah']->id, 
                 $this->employees['mike']->id
             ])->update(['reporting_to' => $boss->user_id]);
        }
    }

    private function createEmployee($first, $last, $code, $dept, $desig, $ctc)
    {
        // User
        $user = User::firstOrCreate(
            ['email' => strtolower($first).'@company.com'],
            ['name' => "$first $last", 'password' => Hash::make('password'), 'tenant_id' => 1]
        );
        $user->assignRole('Employee');
        if ($code === 'MGR001') $user->assignRole('Manager');

        // Employee
        $emp = Employee::updateOrCreate(
            ['employee_code' => $code],
            [
                'user_id' => $user->id,
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => $first,
                'last_name' => $last,
                'department_id' => $dept->id,
                'designation' => $desig,
                'joining_date' => Carbon::now()->subYears(2),
                'status' => 'active',
                'gender' => ($first === 'Sarah' || $first === 'Emma') ? 'Female' : 'Male',
                'tenant_id' => 1
            ]
        );

        // Salary
        EmployeeSalary::updateOrCreate(
            ['employee_id' => $emp->id],
            [
                'annual_ctc' => $ctc,
                'salary_structure_id' => 1,
                'effective_date' => Carbon::now()->subYears(2),
                'breakdown' => json_encode([
                    'basic' => $ctc * 0.4,
                    'hra' => $ctc * 0.2,
                    'special_allowance' => $ctc * 0.4
                ]),
                'is_active' => true
            ]
        );

        return $emp;
    }

    private function seedHistory()
    {
        $months = ['2025-10-01', '2025-11-01', '2025-12-01'];

        // John's Active Loan (Started Oct)
        $loan = Loan::create([
            'employee_id' => $this->employees['john']->id,
            'loan_product_id' => LoanProduct::where('name', 'Personal Loan')->first()->id,
            'principal_amount' => 100000,
            'monthly_installment' => 10000, // 10 Months
            'status' => 'Active',
            'approved_at' => Carbon::parse('2025-10-05'),
            'disbursement_date' => Carbon::parse('2025-10-10'),
            'tenure_months' => 10
        ]);

        foreach ($months as $dateStr) {
            $date = Carbon::parse($dateStr);
            $batch = "PAY-" . $date->format('M-Y');
            
            // Calculate approximate payroll cost
            // Sum of all seeded CTCs / 12
            $monthlyCost = (1800000 + 1200000 + 600000 + 500000 + 3000000) / 12;

            // Create Payroll Batch
            $payroll = Payroll::create([
                'batch_name' => $batch,
                'month' => $date->month,
                'year' => $date->year,
                'start_date' => $date->startOfMonth(),
                'end_date' => $date->endOfMonth(),
                'status' => 'Paid',
                'total_payout' => $monthlyCost, 
                'processed_by' => 1 // System
            ]);

            // Simulate Claims (Approved)
            Expense::create([
                'employee_id' => $this->employees['sarah']->id,
                'expense_category_id' => ExpenseCategory::where('name', 'Local Travel')->first()->id,
                'title' => 'Client Visit Travel',
                'category' => 'Local Travel',
                'amount' => rand(1500, 3000),
                'incurred_date' => $date->copy()->addDays(5),
                'status' => 'Paid',
                'payroll_id' => $payroll->id
            ]);

            // Loan Deduction (If applicable)
            if ($date->gt(Carbon::parse('2025-10-30'))) {
                 LoanRepayment::create([
                     'loan_id' => $loan->id,
                     'amount' => 10000,
                     'scheduled_date' => $date->copy()->endOfMonth(),
                     'status' => 'Paid',
                     'payroll_id' => $payroll->id
                 ]);
            }
        }
    }

    private function seedCurrent()
    {
        // JAN 2026 (Live)
        
        // 1. Pending Claim (Manager Approval needed)
        Expense::create([
            'employee_id' => $this->employees['mike']->id,
            'expense_category_id' => ExpenseCategory::where('name', 'Internet')->first()->id,
            'title' => 'Wifi Recharge',
            'category' => 'Internet',
            'amount' => 1200,
            'incurred_date' => Carbon::now()->subDays(2),
            'status' => 'Pending Approval', // Needs Boss Action
            'description' => 'Wifi Bill Jan'
        ]);

        // 2. Pending Loan Request
        Loan::create([
             'employee_id' => $this->employees['mike']->id,
             'loan_product_id' => LoanProduct::where('name', 'Salary Advance')->first()->id,
             'principal_amount' => 20000,
             'tenure_months' => 1,
             'monthly_installment' => 20000, 
             'status' => 'Submitted',
             'reason' => 'Emergency Medical'
        ]);

        // 3. Tax Declarations
        // 3. Tax Declarations
        // Create a Section first if needed or assume ID 1 exists (80C)
        $section80C = \Illuminate\Support\Facades\DB::table('tax_sections')->where('section_code', '80C')->first();
        if (!$section80C) {
             $section80CId = \Illuminate\Support\Facades\DB::table('tax_sections')->insertGetId([
                 'name' => 'Life Insurance', 'section_code' => '80C', 'max_deduction' => 150000, 'is_active' => true
             ]);
        } else {
             $section80CId = $section80C->id;
        }

        $declaration = TaxDeclaration::create([
            'employee_id' => $this->employees['john']->id,
            'tax_section_id' => $section80CId,
            'fiscal_year' => '2025-2026',
            'declared_amount' => 150000,
            'status' => 'Submitted'
        ]);

        // 4. Pending Proofs linked to Declaration
        InvestmentProof::create([
             'tax_declaration_id' => $declaration->id,
             'file_path' => 'dummy/lic_receipt.pdf',
             'file_name' => 'lic_receipt.pdf'
        ]);
        
        // 5. Draft Payroll for Jan
        
        // 6. Performance Management (Seeding)
        // Cycle
        $cycle = \App\Models\Performance\AppraisalCycle::firstOrCreate(
            ['name' => 'Annual Appraisal 2025'],
            [
                'start_date' => '2025-04-01',
                'end_date' => '2026-03-31',
                'self_review_deadline' => '2026-03-15',
                'status' => 'Active',
                'is_active' => true
            ]
        );

        // Goals for John (Senior Engineer)
        // Goal 1: Delivery
        $goal1 = \App\Models\Performance\Goal::create([
             'employee_id' => $this->employees['john']->id, 
             'appraisal_cycle_id' => $cycle->id,
             'title' => 'Deliver Payroll v2.0',
             'description' => 'Ensure zero bugs in production release.',
             'weightage' => 40,
             'status' => 'Approved',
             'progress' => 80,
             'progress_status' => 'In Progress'
        ]);
        
        // Goal 2: Mentorship
        $goal2 = \App\Models\Performance\Goal::create([
             'employee_id' => $this->employees['john']->id, 
             'appraisal_cycle_id' => $cycle->id,
             'title' => 'Mentor Junior Devs',
             'description' => 'Train Mike on Laravel.',
             'weightage' => 20,
             'status' => 'Approved',
             'progress' => 50,
             'progress_status' => 'In Progress'
        ]);

        // Initiate Appraisal for John
        \App\Models\Performance\Appraisal::create([
            'employee_id' => $this->employees['john']->id,
            'appraisal_cycle_id' => $cycle->id,
            'stage' => 'Self Review',
            'self_rating' => 4.5,
            'self_comments' => 'Met all targets so far.'
        ]);
    }
}
