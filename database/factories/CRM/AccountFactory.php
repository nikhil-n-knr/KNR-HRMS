<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Account;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::first()->id ?? 1,
            'name' => $this->faker->company,
            'industry' => $this->faker->randomElement(['SaaS', 'FinTech', 'E-commerce', 'Healthcare', 'Real Estate']),
            'website' => $this->faker->url,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'country' => 'USA',
            'created_by' => User::first()->id ?? 1,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
