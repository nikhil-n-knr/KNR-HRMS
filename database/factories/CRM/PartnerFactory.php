<?php

namespace Database\Factories\CRM;

use App\Models\CRM\Partner;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        $name = $this->faker->company;
        return [
            'tenant_id' => Tenant::first()->id ?? 1,
            'name' => $name,
            'email' => $this->faker->unique()->companyEmail,
            'referral_code' => strtoupper(Str::slug($name) . '-' . Str::random(3)),
            'website' => $this->faker->url,
            'commission_rate' => $this->faker->randomElement([5, 10, 15, 20]),
            'status' => 'active',
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
