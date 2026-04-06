<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'tenant_id' => 1,
            'employee_code' => 'EMP-' . fake()->unique()->numerify('#####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'designation' => fake()->jobTitle(),
            'joining_date' => fake()->date(),
            'status' => 'active', // 'active', 'probation', 'notice_period', 'terminated', 'resigned', 'on_leave'
            'employment_type' => 'full_time', // 'full_time', 'part_time', 'contract', 'intern'
        ];
    }
}
