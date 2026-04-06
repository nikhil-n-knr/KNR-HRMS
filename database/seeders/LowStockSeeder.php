<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

class LowStockSeeder extends Seeder
{
    public function run(): void
    {
        // Simulate consumption to get some items below min_stock_level
        $items = InventoryItem::inRandomOrder()->take(8)->get();

        foreach ($items as $item) {
            // Set current_stock below min_stock_level to trigger low-stock
            $deficit = rand(1, (int) max(1, $item->min_stock_level * 0.8));
            $newStock = max(0, $item->min_stock_level - $deficit);
            $item->update(['current_stock' => $newStock]);
        }

        $this->command->info('Set ' . $items->count() . ' inventory items below min stock level');
    }
}
