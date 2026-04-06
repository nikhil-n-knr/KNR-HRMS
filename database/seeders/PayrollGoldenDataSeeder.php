<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Employee;
use App\Models\SalaryStructure;
use App\Models\EmployeeSalary;
use App\Models\EmployeeTaxRegime;
use App\Models\EmployeeHraDeclaration;
use App\Models\TaxDeclaration;
use App\Models\TaxSection;
use App\Models\InvestmentProof;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\LoanRepayment;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\Department;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Payroll\SalaryService;

class PayrollGoldenDataSeeder extends Seeder
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    public function run()
    {
        $tenant = Tenant::first() ?? Tenant::create(['name' => 'Golden Corp', 'slug' => 'golden', 'domain' => 'golden.test']);
        $admin = User::where('email', 'admin@test.com')->first();
        $structure = SalaryStructure::where('name', 'Review (Indian Standard)')->with('components')->first() ?? SalaryStructure::first();

        $depts = Department::pluck('id', 'name');
        $locs = Location::pluck('id', 'name');

        $fiscalYear = "2025-2026";

        $employeeData = [
            [
                'first_name' => 'Vikram', 'last_name' => 'Malhotra', 'designation' => 'VP Engineering', 
                'annual_ctc' => 5000000, 'regime' => 'Old', 'rent' => 60000, 'is_metro' => true,
                'dept' => 'Engineering', 'email' => 'vikram@golden.test'
            ],
            [
                'first_name' => 'Anjali', 'last_name' => 'Sharma', 'designation' => 'HR Manager', 
                'annual_ctc' => 2500000, 'regime' => 'Old', 'rent' => 35000, 'is_metro' => true,
                'dept' => 'HR', 'email' => 'anjali@golden.test'
            ],
            [
                'first_name' => 'Rahul', 'last_name' => 'Verma', 'designation' => 'Senior Developer', 
                'annual_ctc' => 1800000, 'regime' => 'New', 'rent' => 25000, 'is_metro' => false,
                'dept' => 'Engineering', 'email' => 'rahul@golden.test'
            ],
            [
                'first_name' => 'Sneha', 'last_name' => 'Kapur', 'designation' => 'Software Engineer', 
                'annual_ctc' => 1200000, 'regime' => 'New', 'rent' => 20000, 'is_metro' => false,
                'dept' => 'Engineering', 'email' => 'sneha@golden.test'
            ],
            [
                'first_name' => 'Amit', 'last_name' => 'Patel', 'designation' => 'Recruiter', 
                'annual_ctc' => 800000, 'regime' => 'Old', 'rent' => 15000, 'is_metro' => false,
                'dept' => 'HR', 'email' => 'amit@golden.test'
            ],
        ];

        // Ensure Loan Products
        $personalLoan = LoanProduct::firstOrCreate(['name' => 'Personal Loan'], ['interest_rate' => 12, 'max_tenure_months' => 60]);
        $salaryAdvance = LoanProduct::firstOrCreate(['name' => 'Salary Advance'], ['interest_rate' => 0, 'max_tenure_months' => 3]);

        // Ensure Expense Categories
        $travelCat = ExpenseCategory::firstOrCreate(['name' => 'Travel']);
        $foodCat = ExpenseCategory::firstOrCreate(['name' => 'Food']);

        // Tax Sections
        $sec80C = TaxSection::where('section_code', '80C')->first();
        $sec80D = TaxSection::where('section_code', '80D')->first();

        $employees = [];

        foreach ($employeeData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'password' => Hash::make('password'),
                    'tenant_id' => $tenant->id,
                    'status' => 'active'
                ]
            );

            $emp = Employee::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'uuid' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'employee_code' => 'GOLD-' . rand(1000, 9999),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'designation' => $data['designation'],
                    'department_id' => $depts[$data['dept']] ?? $depts->first(),
                    'location_id' => $locs->first(),
                    'joining_date' => Carbon::now()->subYears(2),
                    'status' => 'active',
                    'pan_number' => strtoupper(Str::random(10)),
                    'uan_number' => rand(100000000000, 999999999999),
                ]
            );

            $employees[] = $emp;

            // Assign Roles
            $adminUser = \App\Models\User::where('email', 'admin@test.com')->first();
            $adminId = $adminUser ? $adminUser->id : 1;

            if ($data['email'] === 'anjali@golden.test') {
                $role = \App\Models\Role::firstOrCreate(['slug' => 'hr_admin'], ['name' => 'HR Admin', 'tenant_id' => $tenant->id, 'created_by' => $adminId]);
                $user->roles()->syncWithoutDetaching([$role->id => ['assigned_by' => $adminId, 'is_active' => true, 'valid_from' => now()]]);
            } elseif ($data['email'] === 'vikram@golden.test') {
                $role = \App\Models\Role::firstOrCreate(['slug' => 'admin_legacy'], ['name' => 'Admin', 'tenant_id' => $tenant->id, 'created_by' => $adminId]);
                $user->roles()->syncWithoutDetaching([$role->id => ['assigned_by' => $adminId, 'is_active' => true, 'valid_from' => now()]]);
            } elseif ($data['email'] === 'rahul@golden.test') {
                $role = \App\Models\Role::firstOrCreate(['slug' => 'manager_legacy'], ['name' => 'Manager', 'tenant_id' => $tenant->id, 'created_by' => $adminId]);
                $user->roles()->syncWithoutDetaching([$role->id => ['assigned_by' => $adminId, 'is_active' => true, 'valid_from' => now()]]);
            }

            // 1. Salary & Breakdown
            $breakdown = $this->salaryService->calculateBreakdown($data['annual_ctc'], $structure->id);

            EmployeeSalary::updateOrCreate(
                ['employee_id' => $emp->id, 'salary_structure_id' => $structure->id],
                [
                    'annual_ctc' => $data['annual_ctc'],
                    'effective_date' => Carbon::now()->startOfYear(),
                    'breakdown' => $breakdown,
                    'is_active' => true
                ]
            );

            // 2. Tax Regime
            EmployeeTaxRegime::updateOrCreate(
                ['employee_id' => $emp->id, 'fiscal_year' => $fiscalYear],
                ['regime' => $data['regime']]
            );

            // 3. HRA
            EmployeeHraDeclaration::updateOrCreate(
                ['employee_id' => $emp->id, 'fiscal_year' => $fiscalYear],
                [
                    'rent_monthly' => $data['rent'],
                    'is_metro_city' => $data['is_metro'],
                    'landlord_name' => 'Landlord of ' . $data['first_name'],
                    'landlord_pan' => strtoupper(Str::random(10)),
                    'rented_address' => rand(1, 999) . ', ' . ($data['is_metro'] ? 'Metro City Main St' : 'Suburban Colony'),
                    'status' => 'Verified'
                ]
            );

            // 4. Tax Declarations
            if ($data['regime'] === 'Old' && $sec80C) {
                TaxDeclaration::updateOrCreate(
                    ['employee_id' => $emp->id, 'tax_section_id' => $sec80C->id, 'fiscal_year' => $fiscalYear],
                    [
                        'declared_amount' => 150000,
                        'verified_amount' => 150000,
                        'status' => 'Verified'
                    ]
                );

                if ($sec80D) {
                    TaxDeclaration::updateOrCreate(
                        ['employee_id' => $emp->id, 'tax_section_id' => $sec80D->id, 'fiscal_year' => $fiscalYear],
                        [
                            'declared_amount' => 25000,
                            'verified_amount' => 0,
                            'status' => 'Submitted'
                        ]
                    );
                }
            }

            // 5. Expenses
            Expense::updateOrCreate(
                ['employee_id' => $emp->id, 'title' => 'Client Visit - Bangalore'],
                [
                    'incurred_date' => Carbon::now()->subDays(10),
                    'expense_category_id' => $travelCat->id,
                    'category' => 'Travel',
                    'amount' => 5000,
                    'status' => 'Approved'
                ]
            );

            Expense::updateOrCreate(
                ['employee_id' => $emp->id, 'title' => 'Team Lunch'],
                [
                    'incurred_date' => Carbon::now()->subDays(5),
                    'expense_category_id' => $foodCat->id,
                    'category' => 'Food',
                    'amount' => 2000,
                    'status' => 'Pending'
                ]
            );

            // 6. Loans
            if ($data['first_name'] === 'Vikram') {
                $loan = Loan::updateOrCreate(
                    ['employee_id' => $emp->id, 'loan_product_id' => $personalLoan->id],
                    [
                        'loan_type' => 'Personal Loan',
                        'principal_amount' => 500000,
                        'tenure_months' => 24,
                        'monthly_installment' => 25000,
                        'interest_rate' => 12,
                        'status' => 'Active',
                        'disbursement_date' => Carbon::now()->subMonths(6)
                    ]
                );

                for ($i = 1; $i <= 6; $i++) {
                    LoanRepayment::updateOrCreate(
                        ['loan_id' => $loan->id, 'scheduled_date' => Carbon::now()->subMonths($i)->day(5)->format('Y-m-d')],
                        [
                            'amount' => 25000,
                            'status' => 'Paid'
                        ]
                    );
                }
            }
        }

        // 7. Payroll History (Nov, Dec, Jan)
        $months = [
            ['month' => 11, 'year' => 2025, 'name' => 'November 2025'],
            ['month' => 12, 'year' => 2025, 'name' => 'December 2025'],
            ['month' => 1, 'year' => 2026, 'name' => 'January 2026'],
        ];

        foreach ($months as $m) {
            $payroll = Payroll::updateOrCreate(
                ['month' => $m['month'], 'year' => $m['year']],
                [
                    'batch_name' => $m['name'],
                    'start_date' => Carbon::create($m['year'], $m['month'], 1),
                    'end_date' => Carbon::create($m['year'], $m['month'], 1)->endOfMonth(),
                    'status' => 'Completed',
                    'processed_by' => $admin->id,
                    'processed_at' => Carbon::create($m['year'], $m['month'], 28)
                ]
            );

            foreach ($employees as $emp) {
                $salary = $emp->salaries()->where('is_active', true)->first();
                $breakdownData = $salary->breakdown;
                
                $earningsBreakdown = $breakdownData['components'];
                $deductionsBreakdown = []; // Assuming components contains both, we might need to split

                // Split earnings and deductions for the payslip explicitly
                $earnings = [];
                $deductions = [];
                foreach ($earningsBreakdown as $name => $amount) {
                    $comp = $structure->components->firstWhere('name', $name);
                    if ($comp && $comp->type === 'deduction') {
                        $deductions[$name] = $amount;
                    } else {
                        $earnings[$name] = $amount;
                    }
                }

                Payslip::updateOrCreate(
                    ['payroll_id' => $payroll->id, 'employee_id' => $emp->id],
                    [
                        'payslip_number' => 'PS-' . $m['year'] . $m['month'] . '-' . $emp->id,
                        'basic_salary' => $earnings['Basic Salary'] ?? 0,
                        'gross_earnings' => $breakdownData['gross_earnings'],
                        'gross_deductions' => $breakdownData['total_deductions'],
                        'net_pay' => $breakdownData['net_pay'],
                        'payable_days' => 30,
                        'lop_days' => 0,
                        'earnings_breakdown' => $earnings,
                        'deductions_breakdown' => $deductions,
                        'status' => 'Paid'
                    ]
                );
            }
        }

        $this->command->info('Golden Data Seeded Successfully with Breakdowns!');
    }
}
