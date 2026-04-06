<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Asset;
use Illuminate\Support\Str;

class VendorPortalController extends Controller
{
    /**
     * Show Upload Page.
     * Accessible via a signed link or token.
     */
    public function showUpload(Request $request)
    {
        // Simple security check (simulation)
        if (!$request->has('token')) {
            abort(403, 'Unauthorized Access');
        }

        return Inertia::render('Vendor/Upload', [
            'token' => $request->token,
            'categories' => \App\Models\AssetCategory::select('id', 'name')->get()
        ]);
    }

    /**
     * Process CSV Upload.
     */
    public function processUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'token' => 'required',
            'po_number' => 'required|string'
        ]);

        $file = $request->file('csv_file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($data); // Assume first row is header

        $importedCount = 0;

        foreach ($data as $row) {
            if (count($row) < 3) continue; // Basic skip

            // Expected Format: Serial Number, Model Name, Category Name/ID, Purchase Cost
            // Mapping (Simplified for now, Phase 11 will make this smart)
            try {
                $serial = $row[0] ?? Str::uuid();
                $name = $row[1] ?? 'Unknown Asset';
                $categoryName = $row[2] ?? 'Laptop';
                $cost = $row[3] ?? 0;
                
                // Find Category
                $category = \App\Models\AssetCategory::where('name', $categoryName)->first();
                $categoryId = $category ? $category->id : 1; // Fallback

                Asset::create([
                    'tenant_id' => 1, // Default Tenant
                    'category_id' => $categoryId,
                    'name' => $name,
                    'serial_number' => $serial,
                    'status' => 'Draft', // Needed migration
                    'purchase_cost' => $cost,
                    'purchase_date' => now(),
                    'meta' => ['po_number' => $request->po_number]
                ]);

                $importedCount++;

            } catch (\Exception $e) {
                // Ignore errors for now or log
                continue;
            }
        }

        return back()->with('success', "Successfully imported {$importedCount} assets [Draft Mode].");
    }
}
