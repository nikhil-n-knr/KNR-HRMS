<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreDataSeeder extends Seeder
{
    public function run()
    {
        $tenantId = 1;
        $siteId = 1;

        // Categories mapping
        $cats = [
            'Men' => 1,
            'Casual Shirts' => 2,
            'Formal Shirts' => 3,
            'Polo Shirts' => 4,
            'Plain T-Shirts' => 5,
            'Women' => 11,
            'Shoes' => 18,
            'Men Casual Shoes' => 19,
            'Men Formal Shoes' => 20,
            'Sports Shoes' => 21,
            'Women Heels' => 22,
            'Women Flats' => 23,
            'Sandals' => 24,
        ];

        $womenSubCats = [
            'Kurtas & Kurtis' => 'women-kurtas',
            'Sarees' => 'women-sarees',
            'Western Wear' => 'western-wear',
            'Ethnic Wear' => 'ethnic-wear',
        ];

        foreach ($womenSubCats as $name => $slug) {
            $existing = DB::table('cms_product_categories')->where('site_id', $siteId)->where('slug', $slug)->first();
            if (!$existing) {
                $id = DB::table('cms_product_categories')->insertGetId([
                    'tenant_id' => $tenantId,
                    'site_id'   => $siteId,
                    'parent_id' => 11,
                    'name'      => $name,
                    'slug'      => $slug,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $cats[$name] = $id;
            } else {
                $cats[$name] = $existing->id;
            }
        }

        // Generate Products
        $types = [
            ['name' => 'Cotton Breeze Casual Shirt', 'cat' => 1, 'sub' => 2, 'prefix' => 'men-shirt', 'price' => 999],
            ['name' => 'Executive Formal Shirt', 'cat' => 1, 'sub' => 3, 'prefix' => 'formal-shirt', 'price' => 1499],
            ['name' => 'SpeedRun Sports Shoe', 'cat' => 18, 'sub' => 21, 'prefix' => 'sport-shoe', 'price' => 2999],
            ['name' => 'Urban Walker Casual', 'cat' => 18, 'sub' => 19, 'prefix' => 'casual-shoe', 'price' => 1999],
            ['name' => 'Floral Embroidered Kurta', 'cat' => 11, 'sub' => $cats['Kurtas & Kurtis'], 'prefix' => 'kurta', 'price' => 899],
            ['name' => 'Silk Elegance Saree', 'cat' => 11, 'sub' => $cats['Sarees'], 'prefix' => 'saree', 'price' => 4500],
        ];

        foreach ($types as $t) {
            for ($i = 1; $i <= 15; $i++) {
                try {
                    $price = $t['price'] + rand(-200, 500);
                    DB::table('cms_products')->insert([
                        'tenant_id' => $tenantId,
                        'site_id'   => $siteId,
                        'category_id' => $t['cat'],
                        'sub_category_id' => $t['sub'],
                        'name'      => $t['name'] . ' ' . $i,
                        'slug'      => $t['prefix'] . '-' . $i . '-' . Str::random(6),
                        'price'     => $price,
                        'mrp'       => $price + rand(500, 1000),
                        'images'    => json_encode(["https://picsum.photos/seed/" . $t['prefix'] . $i . "/800/1000"]),
                        'description' => "Professional quality " . $t['name'] . " for your collection.",
                        'short_description' => "Stylish " . $t['name'],
                        'is_active' => true,
                        'featured'  => rand(0, 100) > 80,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {}
            }
        }
    }
}
