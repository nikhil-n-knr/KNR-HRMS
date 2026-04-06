<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetCategory;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AssetImportController extends Controller
{
    public function create()
    {
        return Inertia::render('Admin/Assets/Import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        
        // Remove Header
        $header = array_shift($data);
        
        // Map header columns to indices if needed, or assume fixed structure:
        // Name, Serial, Category, Purchase Cost, Purchase Date

        $imported = 0;
        $errors = 0;

        DB::beginTransaction();
        try {
            foreach ($data as $row) {
                if (count($row) < 3) continue; // Skip invalid rows

                $name = $row[0] ?? null;
                $serial = $row[1] ?? null;
                $categoryName = $row[2] ?? null;
                $cost = $row[3] ?? 0;
                $date = $row[4] ?? now();

                if (!$name || !$categoryName) {
                    $errors++;
                    continue;
                }

                // Find or Create Category
                $category = AssetCategory::firstOrCreate(['name' => $categoryName]);

                Asset::updateOrCreate(
                    ['serial_number' => $serial], // Unique Key
                    [
                        'name' => $name,
                        'category_id' => $category->id,
                        'purchase_cost' => $cost,
                        'purchase_date' => $date, // Ensure format YYYY-MM-DD in CSV
                        'status' => 'Available',
                        'tenant_id' => auth()->user()->tenant_id
                    ]
                );
                $imported++;
            }
            DB::commit();
            return redirect()->route('admin.assets.index')->with('success', "Imported {$imported} assets successfully. Skipped {$errors} invalid rows.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Import Failed: ' . $e->getMessage());
        }
    }
}
