<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Lead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['new', 'contacted', 'qualified', 'unqualified']);
        $source = $this->faker->randomElement(['website', 'referral', 'social_media', 'cold_call', 'event', 'other']);
        
        return [
            'tenant_id' => Tenant::first()->id ?? 1,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'title' => $this->faker->jobTitle,
            'company' => $this->faker->company,
            'source' => $source,
            'status' => $status,
            'health_score' => $this->faker->numberBetween(20, 95),
            'ai_conversion_probability' => $this->faker->numberBetween(10, 90),
            'ai_insights' => [
                'summary' => $this->faker->sentence,
                'next_step' => 'Follow up via ' . $this->faker->randomElement(['Email', 'WhatsApp', 'Phone']),
                'sentiment' => $this->faker->randomElement(['Positive', 'Neutral', 'Curious'])
            ],
            'notes' => $this->faker->paragraph,
            'created_by' => User::first()->id ?? 1,
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
