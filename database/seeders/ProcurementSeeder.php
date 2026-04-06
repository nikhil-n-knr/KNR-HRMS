<?php

namespace Database\Seeders;

use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::pluck('id');
        $userId  = User::first()->id;

        $statuses = ['Draft', 'Draft', 'Approved', 'Ordered', 'Received'];

        $sampleItems = [
            ['Laptop Dell XPS', 'Office Chair', 'USB-C Hub'],
            ['A4 Paper Reams', 'Printer Ink Cartridge', 'Cable Management'],
            ['Keyboard', 'Mouse', 'Monitor Stand'],
            ['Network Switch 24-port', 'Patch Cables (20)', 'Rack Shelf'],
            ['Server RAM 16GB', 'SSD 1TB', 'Thermal Paste'],
        ];

        for ($i = 0; $i < 20; $i++) {
            $rawItems = $sampleItems[$i % count($sampleItems)];
            $lineItems = [];
            $totalCost = 0;

            foreach ($rawItems as $name) {
                $qty  = rand(1, 10);
                $cost = rand(50, 800);
                $lineItems[] = [
                    'name'      => $name,
                    'quantity'  => $qty,
                    'unit_cost' => $cost,
                ];
                $totalCost += $qty * $cost;
            }

            PurchaseRequest::create([
                'tenant_id'    => 1,
                'vendor_id'    => $vendors->random(),
                'status'       => $statuses[$i % count($statuses)],
                'items'        => $lineItems,
                'total_cost'   => $totalCost,
                'gst_amount'   => round($totalCost * 0.18, 2),
                'created_by'   => $userId,
                'po_number'    => 'PO-' . strtoupper(Str::random(8)),
                'expected_date' => now()->addDays(rand(7, 60)),
            ]);
        }

        $this->command->info('Seeded 20 Purchase Requests');
    }
}
