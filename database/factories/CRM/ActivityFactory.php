<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Activity;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['note', 'task', 'email', 'call', 'meeting', 'sms']);
        $isCompleted = $this->faker->boolean(70);
        
        return [
            'tenant_id' => Tenant::first()->id ?? 1,
            'type' => $type,
            'subject' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'due_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'created_by' => User::first()->id ?? 1,
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
