<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Laptop', 'useful_life_years' => 3, 'is_electronic' => true],
            ['name' => 'Desktop', 'useful_life_years' => 5, 'is_electronic' => true],
            ['name' => 'Monitor', 'useful_life_years' => 5, 'is_electronic' => true],
            ['name' => 'Mobile Phone', 'useful_life_years' => 2, 'is_electronic' => true],
            ['name' => 'Peripheral', 'useful_life_years' => 1, 'is_electronic' => true],
            ['name' => 'Furniture', 'useful_life_years' => 10, 'is_electronic' => false],
            ['name' => 'Vehicle', 'useful_life_years' => 7, 'is_electronic' => false],
            ['name' => 'Software License', 'useful_life_years' => 1, 'is_electronic' => false],
            ['name' => 'Network Equipment', 'useful_life_years' => 4, 'is_electronic' => true],
        ];

        foreach ($categories as $cat) {
            \App\Models\AssetCategory::updateOrCreate(
                ['name' => $cat['name']],
                $cat
            );
        }
    }
}
