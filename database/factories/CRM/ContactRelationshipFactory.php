<?php

namespace Database\Factories\CRM;

use App\Models\CRM\ContactRelationship;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactRelationshipFactory extends Factory
{
    protected $model = ContactRelationship::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::first()->id ?? 1,
            'relation_type' => $this->faker->randomElement(['Alumni', 'Past Colleague', 'Mentor', 'Partner']),
            'strength' => $this->faker->numberBetween(1, 10),
            'notes' => $this->faker->sentence,
            'created_at' => now(),
        ];
    }
}
