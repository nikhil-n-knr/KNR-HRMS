<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Deal;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        $stages = ['lead', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost'];
        $stage = $this->faker->randomElement($stages);
        $value = $this->faker->numberBetween(5000, 250000);
        $probability = match($stage) {
            'lead' => 10,
            'qualified' => 20,
            'proposal' => 50,
            'negotiation' => 80,
            'closed_won' => 100,
            'closed_lost' => 0,
            default => 10,
        };

        $status = match($stage) {
            'closed_won' => 'won',
            'closed_lost' => 'lost',
            default => 'open',
        };

        return [
            'tenant_id' => Tenant::first()->id ?? 1,
            'title' => $this->faker->company . ' - ' . $this->faker->randomElement(['Enterprise Q3', 'Software Implementation', 'Annual License']),
            'value' => $value,
            'stage' => $stage,
            'status' => $status,
            'probability' => $probability,
            'weighted_value' => ($value * $probability) / 100,
            'health_score' => $this->faker->numberBetween(15, 98),
            'ai_insights' => [
                'risk_factor' => $this->faker->randomElement(['Low', 'Medium', 'High']),
                'recommendation' => 'Engage stakeholder: ' . $this->faker->name
            ],
            'created_by' => User::first()->id ?? 1,
            'assigned_to' => User::first()->id ?? 1,
            'expected_close_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
