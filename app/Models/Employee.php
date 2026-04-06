<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FilterableByAccess;
use App\Traits\LogsActivity;

class Employee extends Model
{
    use HasFactory, SoftDeletes, FilterableByAccess, LogsActivity;

    protected $fillable = [
        'uuid',
        'tenant_id',
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department_id',
        'location_id',
        'designation',
        'reporting_to',
        'joining_date',
        'status',
        'employment_type',
        'avatar',
        'allow_loans',
        'uan_number',
        'esi_number',
        'pan_number',
        'is_international_worker',
        'is_director',
        'pf_uncapped',
        'internal_cost_rate', // Added Phase 10
        'exit_date'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'internal_cost_rate', // Hide by default for security
    ];

    protected $casts = [
        'joining_date' => 'date',
        'exit_date' => 'date',
        'allow_loans' => 'boolean',
        'is_international_worker' => 'boolean',
        'is_director' => 'boolean',
        'pf_uncapped' => 'boolean',
        'internal_cost_rate' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    public function personalDetail()
    {
        return $this->hasOne(EmployeePersonalDetail::class);
    }

    public function healthRecord()
    {
        return $this->hasOne(EmployeeHealthRecord::class);
    }

    public function families()
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    public function bankDetails()
    {
        return $this->hasMany(EmployeeBankDetail::class);
    }

    public function currentBankDetail()
    {
        return $this->hasOne(EmployeeBankDetail::class)->where('is_primary', true)->latest();
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function rotations()
    {
        return $this->hasMany(EmployeeRotation::class);
    }

    /**
     * Projects assigned to the employee via WorkAssignment.
     */
    public function projects()
    {
        // Polymorphic Many-to-Many via work_assignments
        return $this->morphToMany(Project::class, 'assignee', 'work_assignments', 'assignee_id', 'project_id')
                    ->wherePivot('assignee_type', self::class)
                    ->withPivot(['allocated_hours', 'start_date', 'end_date'])
                    ->withTimestamps();
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function latestSalary()
    {
        return $this->hasOne(EmployeeSalary::class)->latestOfMany('effective_date');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function taxRegimes()
    {
        return $this->hasMany(EmployeeTaxRegime::class);
    }

    public function taxDeclarations()
    {
        return $this->hasMany(TaxDeclaration::class);
    }

    public function hraDeclarations()
    {
        return $this->hasMany(EmployeeHraDeclaration::class);
    }

    public function exits()
    {
        return $this->hasMany(EmployeeExit::class);
    }

    // Active Exit
    public function currentExit()
    {
        return $this->hasOne(EmployeeExit::class)->latest(); // Assuming latest is the active one if multiple
    }

    public function attendancePolicy()
    {
        return $this->belongsTo(AttendancePolicy::class);
    }

    public function points()
    {
        return $this->hasMany(EmployeePoint::class);
    }

    public function badges()
    {
        return $this->hasMany(EmployeeBadge::class);
    }

    public function getTotalPointsAttribute()
    {
        return $this->points()->sum('points');
    }

    /**
     * Get the effective attendance policy (Employee specific > Department > null)
     */
    public function getEffectiveAttendancePolicyAttribute()
    {
        if ($this->attendancePolicy) {
            return $this->attendancePolicy;
        }

        if ($this->department && $this->department->attendancePolicy) {
            return $this->department->attendancePolicy;
        }

        return null;
    }
}
