<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Contact;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::first()->id ?? 1,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'title' => $this->faker->jobTitle,
            'department' => $this->faker->randomElement(['Executive', 'Sales', 'Engineering', 'HR', 'Finance']),
            'influence_score' => $this->faker->numberBetween(20, 95),
            'linkedin_url' => 'https://linkedin.com/in/' . $this->faker->slug,
            'status' => 'active',
            'created_by' => User::first()->id ?? 1,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
