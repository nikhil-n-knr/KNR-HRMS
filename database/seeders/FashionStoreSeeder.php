<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FashionStoreSeeder extends Seeder
{
    private function getImageUrl(string $tags, string $seed, int $w = 800, int $h = 1000): string
    {
        $lock = abs(crc32($seed)) % 50000;
        return "https://loremflickr.com/{$w}/{$h}/{$tags}?lock={$lock}";
    }

    private int $tenantId = 1;
    private int $siteId;

    public function run(): void
    {
        // ── 1. Ensure a tenant exists ──
        if (!DB::table('tenants')->where('id', $this->tenantId)->exists()) {
            DB::table('tenants')->insert([
                'id'         => $this->tenantId,
                'name'       => 'Fashion Demo Tenant',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ── 2. Create (or reuse) the e-commerce site ──
        $site = DB::table('cms_sites')
            ->where('tenant_id', $this->tenantId)
            ->where('type', 'ecommerce')
            ->first();

        if (!$site) {
            $this->siteId = DB::table('cms_sites')->insertGetId([
                'tenant_id'          => $this->tenantId,
                'name'               => 'StyleHub Fashion Store',
                'slug'               => 'stylehub',
                'domain'             => null,
                'type'               => 'ecommerce',
                'status'             => 'live',
                'is_live'            => true,
                'currency'           => 'INR',
                'razorpay_key_id'    => 'rzp_test_demo_key_id',
                'razorpay_key_secret'=> 'rzp_test_demo_secret',
                'settings'           => json_encode([
                    'free_shipping_threshold' => 999,
                    'flat_shipping_rate'      => 49,
                    'cod_enabled'             => true,
                    'whatsapp_number'         => '+919876543210',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $this->siteId = $site->id;
        }

        // ── 3. Create theme ──
        $themeId = DB::table('cms_themes')->insertGetId([
            'tenant_id'    => $this->tenantId,
            'name'         => 'StyleHub Theme',
            'is_active'    => true,
            'css_framework'=> 'tailwind_v4',
            'colors'       => json_encode(['primary'=>'#7c3aed','secondary'=>'#ec4899','accent'=>'#f59e0b']),
            'typography'   => json_encode(['font_heading'=>'Inter','font_body'=>'Inter']),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        DB::table('cms_sites')->where('id', $this->siteId)->update(['theme_id' => $themeId]);

        // ── 4. Categories ──
        $cats = $this->seedCategories();

        // ── 5. Products ──
        $this->seedProducts($cats);

        // ── 6. Coupons ──
        $this->seedCoupons();

        // ── 7. Homepage page + sections ──
        $this->seedHomePage();

        $this->command->info("✅ FashionStoreSeeder complete. Site ID: {$this->siteId}");
    }

    // ── CATEGORIES ────────────────────────────────────────────────────
    private function seedCategories(): array
    {
        $cats = [];

        $roots = [
            ['name'=>'Men',   'slug'=>'men',   'gender'=>'men',   'tags'=>'mens,fashion'],
            ['name'=>'Women', 'slug'=>'women', 'gender'=>'women', 'tags'=>'womens,fashion'],
            ['name'=>'Shoes', 'slug'=>'shoes', 'gender'=>'unisex','tags'=>'shoes,footwear'],
        ];

        $children = [
            'men' => [
                ['name'=>'Casual Shirts','slug'=>'men-casual-shirts','gender'=>'men'],
                ['name'=>'Formal Shirts','slug'=>'men-formal-shirts','gender'=>'men'],
                ['name'=>'Polo Shirts',  'slug'=>'men-polo-shirts',  'gender'=>'men'],
                ['name'=>'Plain T-Shirts','slug'=>'men-plain-tshirts','gender'=>'men'],
                ['name'=>'Printed T-Shirts','slug'=>'men-printed-tshirts','gender'=>'men'],
                ['name'=>'Graphic Tees', 'slug'=>'men-graphic-tees', 'gender'=>'men'],
                ['name'=>'Slim Jeans',   'slug'=>'men-slim-jeans',   'gender'=>'men'],
                ['name'=>'Straight Jeans','slug'=>'men-straight-jeans','gender'=>'men'],
                ['name'=>'Jackets',      'slug'=>'men-jackets',      'gender'=>'men'],
            ],
            'women' => [
                ['name'=>'Kurtis',       'slug'=>'women-kurtis',     'gender'=>'women'],
                ['name'=>'Sarees',       'slug'=>'women-sarees',     'gender'=>'women'],
                ['name'=>'Tops',         'slug'=>'women-tops',       'gender'=>'women'],
                ['name'=>'Dresses',      'slug'=>'women-dresses',    'gender'=>'women'],
                ['name'=>'Leggings',     'slug'=>'women-leggings',   'gender'=>'women'],
                ['name'=>'Co-ord Sets',  'slug'=>'women-coord-sets', 'gender'=>'women'],
            ],
            'shoes' => [
                ['name'=>'Men Casual Shoes', 'slug'=>'men-casual-shoes','gender'=>'men'],
                ['name'=>'Men Formal Shoes', 'slug'=>'men-formal-shoes','gender'=>'men'],
                ['name'=>'Sports Shoes',     'slug'=>'sports-shoes',    'gender'=>'unisex'],
                ['name'=>'Women Heels',      'slug'=>'women-heels',     'gender'=>'women'],
                ['name'=>'Women Flats',      'slug'=>'women-flats',     'gender'=>'women'],
                ['name'=>'Sandals',          'slug'=>'sandals',         'gender'=>'unisex'],
            ],
        ];

        foreach ($roots as $i => $root) {
            $id = DB::table('cms_product_categories')->insertGetId([
                'tenant_id'  => $this->tenantId,
                'site_id'    => $this->siteId,
                'parent_id'  => null,
                'name'       => $root['name'],
                'slug'       => $root['slug'],
                'image'      => $this->getImageUrl($root['tags'], $root['slug'], 600, 600),
                'description'=> "Browse our {$root['name']} collection",
                'order'      => $i,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $cats[$root['slug']] = ['id'=>$id, 'gender'=>$root['gender']];

            foreach ($children[$root['slug']] as $j => $child) {
                $tags = $root['gender'] === 'women' ? 'womens,fashion,clothing' : ($root['gender'] === 'men' ? 'mens,fashion,clothing' : 'shoes,footwear');
                $cid = DB::table('cms_product_categories')->insertGetId([
                    'tenant_id'  => $this->tenantId,
                    'site_id'    => $this->siteId,
                    'parent_id'  => $id,
                    'name'       => $child['name'],
                    'slug'       => $child['slug'],
                    'image'      => $this->getImageUrl($tags, $child['slug'], 600, 600),
                    'description'=> "Shop {$child['name']}",
                    'order'      => $j,
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $cats[$child['slug']] = ['id'=>$cid, 'gender'=>$child['gender']];
            }
        }

        return $cats;
    }

    // ── PRODUCTS ───────────────────────────────────────────────────────
    private function seedProducts(array $cats): void
    {
        $batches = [
            // [category_slug, brand, names[], gender, price_min, price_max, tags]
            ['men-casual-shirts', 'Levi\'s', ['Classic Oxford Shirt','Summer Linen Shirt','Slim Fit Check Shirt','Denim Overshirt','Striped Casual Shirt','Chambray Shirt','Floral Print Shirt','Solid Tencel Shirt','Resort Shirt','Campus Stripe'], 'men', 499, 2499, 'mens,shirt'],
            ['men-formal-shirts', 'Van Heusen', ['Premium White Formal','Blue Herringbone','Slim Fit Poplin','French Cuff Shirt','Spread Collar Formal','Business Blue Formal','Pinstripe Formal','Midnight Black Formal','Tuxedo Formal','Sunday Formal'], 'men', 799, 3499, 'mens,formal,shirt'],
            ['men-polo-shirts', 'Polo Ralph Lauren', ['Classic Polo','Performance Polo','Tech Polo','Pique Polo','Striped Polo','Slim Polo','Weekend Polo','Sport Polo','CEO Polo','Campus Polo'], 'men', 699, 2999, 'polo,mens,shirt'],
            ['men-plain-tshirts', 'H&M', ['Essential White Tee','Midnight Black Tee','Navy Blue Tee','Olive Green Tee','Charcoal Tee','Bone White Tee','Ash Grey Tee','Sky Blue Tee','Burgundy Tee','Forest Green Tee'], 'men', 299, 899, 'mens,tshirt'],
            ['men-printed-tshirts', 'Bewakoof', ['Wanderlust Print Tee','Mountain Print Tee','City Skyline Tee','Stay Humble Tee','Abstract Print Tee','Retro Surf Tee','Tokyo Map Tee','Galaxy Print Tee','Music Waves Tee','Forest Trail Tee'], 'men', 349, 999, 'mens,tshirt,streetwear'],
            ['men-graphic-tees', 'Noise', ['Skull Art Tee','Vintage Band Tee','Street Art Tee','Comic Book Tee','Anime Print Tee','Pop Art Tee','Graffiti Tee','90s Nostalgia Tee','Geometric Tee','Psychedelic Tee'], 'men', 399, 1299, 'mens,tshirt,graphic'],
            ['men-slim-jeans', 'Levi\'s', ['511 Slim Dark','Slim Indigo Wash','Slim Ripped Knee','Slim Raw Denim','Slim Light Wash','Slim Stretch','Slim Black Denim','Slim Grey Wash','Slim Mid Rise','Slim Vintage Blue'], 'men', 999, 3499, 'mens,jeans,denim'],
            ['men-straight-jeans', 'Wrangler', ['Straight Dark Blue','Straight Classic Wash','Straight Black','Straight Light Blue','Straight Retro Tint','Straight Comfort','Straight Regular Fit','Straight Stone Wash','Straight Relaxed','Straight Cargo Style'], 'men', 899, 2999, 'mens,jeans'],
            ['men-jackets', 'Roadster', ['Puffer Jacket Black','Denim Jacket Washed','Bomber Jacket Olive','Windbreaker Blue','Leather Biker Jacket','Fleece Zip Jacket','Trench Coat Camel','Quilted Vest Jacket','Track Jacket Navy','Rain Jacket Grey'], 'men', 999, 4999, 'mens,jacket,coat'],
            // Women
            ['women-kurtis', 'Biba', ['Anarkali Floral Kurti','Straight Collar Kurti','A-Line Printed Kurti','Embroidered Kurti','Chikankari Kurti','Block Print Kurti','Ikat Print Kurti','Cotton Daily Wear Kurti','Palazzo Kurti Set','Mirror Work Kurti'], 'women', 499, 2499, 'womens,kurti,indian'],
            ['women-sarees', 'Nalli', ['Kanjivaram Silk Saree','Banarasi Silk Saree','Chiffon Printed Saree','Georgette Party Saree','Cotton Casual Saree','Linen Saree','Organza Saree','Net Embellished Saree','Bandhani Saree','Chanderi Saree'], 'women', 799, 4999, 'womens,saree,indian'],
            ['women-tops', 'Zara', ['Off-Shoulder Ruffle Top','Linen Shirt Top','Crop Tie-Front Top','Boxy Oversized Top','V-Neck Fitted Top','Floral Wrap Top','Cami Satin Top','Button-Down Top','Striped Top','Smocked Top'], 'women', 399, 1799, 'womens,top,shirt'],
            ['women-dresses', 'Mango', ['Floral Maxi Dress','Bodycon Mini Dress','Wrap Midi Dress','Off-Shoulder Dress','A-Line Summer Dress','Shift Dress','Shirt Dress','Slip Dress','Boho Dress','Power Suit Dress'], 'women', 699, 3999, 'womens,dress'],
            ['women-leggings', 'Jockey', ['High-Waist Black Legging','Printed Yoga Legging','Flared Palazzo Legging','Ankle Length Legging','Jeggings Denim Look','Cropped Legging','Sports Compression Legging','Printed Churidar','Solid Churidar','Cotton Kurti Legging'], 'women', 299, 1299, 'womens,leggings,pants'],
            ['women-coord-sets', 'AND', ['Floral Co-ord Set','Linen Co-ord Suit','Crop Top + Skirt Set','Blazer Co-ord','Printed Kurti Set','Tie-Dye Co-ord Set','Striped Co-ord','Embroidered Set','Solid Co-ord Set','Casual Set'], 'women', 999, 3999, 'womens,fashion,suit'],
            // Shoes
            ['men-casual-shoes', 'Puma', ['Classic Canvas Sneaker','Slip-On Loafer','Suede Casual','Derby Lace-Up','Moccasin Leather','Espadrille','Boat Shoe','Driving Shoe','Weekend Sneaker','Urban Runner'], 'men', 799, 3999, 'mens,shoes,sneakers'],
            ['men-formal-shoes', 'Clarks', ['Oxford Brogue','Derby Black','Chelsea Boot','Cap-Toe Oxford','Monk Strap','Penny Loafer','Wing-Tip Brogue','Tassel Loafer','Split-Toe Derby','Plain-Toe Oxford'], 'men', 1499, 4999, 'mens,shoes,formal'],
            ['sports-shoes', 'Nike', ['Air Max Runner','React Training','Zoom Sprint','Cross Trainer','Trail Runner','Circuit Training','Stability Running','Speed Trainer','Gym Trainer','Flex Runner'], 'unisex', 1299, 4999, 'shoes,sports,sneakers'],
            ['women-heels', 'Steve Madden', ['Stiletto Pump','Block Heel Sandal','Kitten Heel','Wedge Heel','Platform Heel','T-Strap Heel','Ankle Strap Heel','Slingback Pump','Mule Heel','Cone Heel'], 'women', 699, 3499, 'womens,heels,shoes'],
            ['women-flats', 'Inc 5', ['Ballet Flat','Point-Toe Flat','Loafer Flat','Bow Flat','Embellished Flat','Canvas Flat','Mule Flat','Slip-On Flat','D\'Orsay Flat','Backless Flat'], 'women', 499, 1999, 'womens,flats,shoes'],
            ['sandals', 'Bata', ['Kolhapuri Sandal','Flip Flop','Birkenstock Style','Gladiator Sandal','Wedge Sandal','Slide Sandal','Ankle Wrap Sandal','Platform Sandal','Beach Sandal','Work Sandal'], 'unisex', 299, 1799, 'sandals,shoes'],
        ];

        $sizes   = ['XS','S','M','L','XL','XXL','XXXL'];
        $colours = ['Black','White','Navy','Grey','Beige','Red','Green','Blue','Brown','Pink'];
        $brands  = ['Levi\'s','H&M','Zara','Van Heusen','Puma','Nike','Bata','Biba','Nalli','Mango'];

        foreach ($batches as $batch) {
            [$catSlug, $brand, $names, $gender, $priceMin, $priceMax, $tags] = $batch;

            if (!isset($cats[$catSlug])) continue;
            $catId = $cats[$catSlug]['id'];

            foreach ($names as $i => $name) {
                $price = rand($priceMin, $priceMax);
                $mrp   = round($price * (1 + rand(20, 60) / 100), -1);
                $discount = round((1 - $price / $mrp) * 100, 1);
                $slug  = Str::slug($name . '-' . $catSlug . '-' . rand(100, 999));

                // Build 6 images
                $images = array_map(fn($n) => [
                    'url'        => $this->getImageUrl($tags, $slug . '-' . $n, 800, 1000),
                    'alt'        => $name,
                    'is_primary' => $n === 0,
                ], range(0, 5));

                // Build size variants
                $variantSizes = ($gender === 'men' || $gender === 'unisex') ? ['S','M','L','XL','XXL'] : ['XS','S','M','L','XL'];
                $variants = [];
                foreach ($variantSizes as $sz) {
                    $variants[] = [
                        'size'  => $sz,
                        'stock' => rand(20, 200),
                        'price' => $price,
                        'sku'   => strtoupper(Str::random(3)) . '-' . $sz,
                    ];
                }

                // Colour swatches
                $productColors = array_slice($colours, rand(0, 4), 4);
                $swatches = array_map(fn($c) => [
                    'name'  => $c,
                    'hex'   => '#' . dechex(rand(0x444444, 0xeeeeee)),
                    'stock' => rand(5, 50),
                ], $productColors);

                DB::table('cms_products')->insert([
                    'tenant_id'          => $this->tenantId,
                    'site_id'            => $this->siteId,
                    'category_id'        => $catId,
                    'brand'              => $brand,
                    'gender'             => $gender,
                    'name'               => $name,
                    'slug'               => $slug,
                    'description'        => "Premium quality {$name}. Crafted for comfort and style. Perfect for everyday wear.",
                    'short_description'  => "Quality {$brand} {$name}",
                    'price'              => $price,
                    'mrp'                => $mrp,
                    'discount_pct'       => $discount,
                    'tax_class'          => 'GST_5',
                    'sku'                => 'SKU-' . strtoupper(Str::random(6)),
                    'stock_qty'          => rand(50, 500),
                    'track_inventory'    => true,
                    'low_stock_threshold'=> 10,
                    'allow_backorder'    => false,
                    'images'             => json_encode($images),
                    'variants'           => json_encode($variants),
                    'variant_attributes' => json_encode([
                        ['name' => 'Size',  'values' => $variantSizes],
                        ['name' => 'Color', 'values' => $productColors],
                    ]),
                    'colour_swatches'    => json_encode($swatches),
                    'sales_count'        => rand(0, 500),
                    'featured'           => $i < 3,
                    'is_active'          => true,
                    'sort_order'         => $i,
                    'seo'                => json_encode([
                        'title'       => "{$name} - Buy Online at StyleHub",
                        'description' => "Shop {$name} from {$brand}. Best price ₹{$price}. Free shipping above ₹999.",
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    // ── COUPONS ────────────────────────────────────────────────────────
    private function seedCoupons(): void
    {
        $coupons = [
            ['code'=>'WELCOME10',  'type'=>'percent','value'=>10, 'desc'=>'10% off on first order', 'min_order'=>0,    'max_disc'=>500],
            ['code'=>'SUMMER30',   'type'=>'percent','value'=>30, 'desc'=>'Summer sale 30% off',    'min_order'=>999,  'max_disc'=>1000],
            ['code'=>'FREESHIP',   'type'=>'free_shipping','value'=>0,'desc'=>'Free shipping',       'min_order'=>499,  'max_disc'=>100],
            ['code'=>'FLAT200',    'type'=>'fixed',  'value'=>200,'desc'=>'₹200 flat discount',     'min_order'=>1499, 'max_disc'=>200],
            ['code'=>'FASHION50',  'type'=>'percent','value'=>50, 'desc'=>'50% off - Flash sale',   'min_order'=>2000, 'max_disc'=>1500],
        ];

        foreach ($coupons as $c) {
            if (!DB::table('cms_coupons')->where('site_id', $this->siteId)->where('code', $c['code'])->exists()) {
                DB::table('cms_coupons')->insert([
                    'tenant_id'       => $this->tenantId,
                    'site_id'         => $this->siteId,
                    'code'            => $c['code'],
                    'description'     => $c['desc'],
                    'type'            => $c['type'],
                    'value'           => $c['value'],
                    'min_order_value' => $c['min_order'],
                    'max_discount'    => $c['max_disc'],
                    'usage_limit'     => null,
                    'per_user_limit'  => 1,
                    'is_active'       => true,
                    'valid_from'      => now(),
                    'valid_until'     => now()->addYear(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    // ── HOMEPAGE ─────────────────────────────────────────────────────
    private function seedHomePage(): void
    {
        // Check if page already exists
        $existing = DB::table('cms_pages')
            ->where('site_id', $this->siteId)
            ->where('slug', '/')
            ->first();

        $blocks = [
            [
                'id'   => 'block-hero',
                'type' => 'hero_video',
                'data' => [
                    'headline'    => 'Summer Collection 2026',
                    'subheadline' => 'Up to 60% OFF on Men, Women & Shoes',
                    'cta_text'    => 'Shop Now →',
                    'cta_url'     => '/men',
                    'bg_image'    => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&q=80&w=1920',
                    'overlay'     => true,
                    'overlay_opacity' => 0.5,
                ],
            ],
            [
                'id'   => 'block-cats',
                'type' => 'category_cards',
                'data' => [
                    'title'      => 'Shop by Category',
                    'categories' => [
                        ['name'=>'Men Shirts',   'url'=>'/men-casual-shirts', 'image'=>'https://images.unsplash.com/photo-1593030761757-71fae45fa0e7?auto=format&fit=crop&w=400&q=80'],
                        ['name'=>'Women Kurtis', 'url'=>'/women-kurtis',      'image'=>'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?auto=format&fit=crop&w=400&q=80'],
                        ['name'=>'Sports Shoes', 'url'=>'/sports-shoes',      'image'=>'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=400&q=80'],
                        ['name'=>'Formal Wear',  'url'=>'/men-formal-shirts', 'image'=>'https://images.unsplash.com/photo-1504593811423-6dd665756598?auto=format&fit=crop&w=400&q=80'],
                        ['name'=>'Dresses',      'url'=>'/women-dresses',     'image'=>'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=400&q=80'],
                        ['name'=>'Jeans',        'url'=>'/men-slim-jeans',    'image'=>'https://images.unsplash.com/photo-1588725895744-8fa298150438?auto=format&fit=crop&w=400&q=80'],
                        ['name'=>'Sarees',       'url'=>'/women-sarees',      'image'=>'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?auto=format&fit=crop&w=400&q=80'],
                        ['name'=>'Sneakers',     'url'=>'/men-casual-shoes',  'image'=>'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=400&q=80'],
                    ],
                ],
            ],
            [
                'id'   => 'block-flash',
                'type' => 'flash_sale',
                'data' => [
                    'title'    => 'Flash Sale',
                    'subtitle' => '1000+ Items — Limited Time Only!',
                    'ends_at'  => now()->addHours(24)->toIso8601String(),
                    'coupon'   => 'SUMMER30',
                ],
            ],
            [
                'id'   => 'block-bestsellers',
                'type' => 'product_carousel',
                'data' => [
                    'title'       => 'Bestsellers',
                    'source'      => 'bestsellers', // controller will hydrate live
                    'limit'       => 12,
                ],
            ],
            [
                'id'   => 'block-brands',
                'type' => 'brand_logos',
                'data' => [
                    'title'  => 'Top Brands',
                    'brands' => ['Levi\'s','Van Heusen','Biba','Puma','Nike','Zara','Mango','H&M','Bata','Wrangler'],
                ],
            ],
            [
                'id'   => 'block-testimonials',
                'type' => 'testimonials',
                'data' => [
                    'title'        => 'What Our Customers Say',
                    'testimonials' => [
                        ['name'=>'Priya S.',  'rating'=>5, 'text'=>'Amazing quality and super fast delivery! Ordered a kurti set and it fits perfectly.', 'avatar'=>'https://picsum.photos/seed/priya/80/80'],
                        ['name'=>'Rahul K.',  'rating'=>5, 'text'=>'The formal shirts are exactly what I needed. Great fabric and stitching.', 'avatar'=>'https://picsum.photos/seed/rahul/80/80'],
                        ['name'=>'Ananya R.', 'rating'=>4, 'text'=>'Loved the summer dresses collection. Will definitely buy more!', 'avatar'=>'https://picsum.photos/seed/ananya/80/80'],
                        ['name'=>'Vikram T.', 'rating'=>5, 'text'=>'Best sneakers deal I found online. Genuine brand, great discount.', 'avatar'=>'https://picsum.photos/seed/vikram/80/80'],
                    ],
                ],
            ],
            [
                'id'   => 'block-newsletter',
                'type' => 'newsletter',
                'data' => [
                    'headline'    => 'Get 10% OFF Your First Order',
                    'subtext'     => 'Subscribe to get exclusive deals, new arrivals & style tips',
                    'placeholder' => 'Enter your email...',
                    'cta'         => 'Subscribe & Save',
                    'coupon'      => 'WELCOME10',
                ],
            ],
        ];

        $layoutData = json_encode(['blocks' => $blocks]);

        if ($existing) {
            DB::table('cms_pages')->where('id', $existing->id)->update([
                'layout_data' => $layoutData,
                'status'      => 'published',
                'updated_at'  => now(),
            ]);
        } else {
            DB::table('cms_pages')->insert([
                'tenant_id'   => $this->tenantId,
                'site_id'     => $this->siteId,
                'title'       => 'Home',
                'slug'        => '/',
                'layout_data' => $layoutData,
                'status'      => 'published',
                'priority'    => 0,
                'seo_meta'    => json_encode([
                    'title'       => 'StyleHub — Fashion for Everyone',
                    'description' => 'Shop 5000+ products — Men, Women & Shoes. Up to 60% off. Free shipping above ₹999. Razorpay secure checkout.',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
