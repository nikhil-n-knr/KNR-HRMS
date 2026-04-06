<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Tenant;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => 'Default Tenant',
            'slug' => 'default-tenant', // Simple slug to avoid issues
            'domain' => 'default.hrms.local',
            'is_active' => true,
        ];
    }
}
