<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Models\Asset;
use Illuminate\Support\Str;

class SmartImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Assets/SmartImport');
    }

    public function inspect(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        
        $path = $request->file('file')->store('imports');
        $headers = $this->getCsvHeaders(storage_path('app/' . $path));
        
        return response()->json([
            'path' => $path,
            'headers' => $headers,
            'db_fields' => [
                'name' => 'Asset Name',
                'serial_number' => 'Serial Number',
                'category' => 'Category (Name)',
                'purchase_cost' => 'Cost',
                'purchase_date' => 'Purchase Date (YYYY-MM-DD)',
                'location' => 'Location (Name)',
                'assigned_to_email' => 'Assigned User (Email)' 
            ]
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
            'mapping' => 'required|array'
        ]);

        $path = storage_path('app/' . $request->path);
        if (!file_exists($path)) abort(404, 'File expired');

        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows); // Skip actual header row if using mapping, OR use mapping index

        $map = $request->mapping; // ['name' => '0', 'serial' => '2'] (Index of CSV column)
        
        $success = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if (count($row) < 2) continue;

                // Extract Data using Map
                $data = [];
                foreach ($map as $dbField => $csvIndex) {
                    if ($csvIndex !== null && isset($row[$csvIndex])) {
                        $data[$dbField] = trim($row[$csvIndex]);
                    }
                }
                
                // --- Validation & Transformation ---
                
                // 1. Check Serial
                if (empty($data['serial_number'])) {
                    throw new \Exception('Missing Serial Number');
                }
                if (Asset::where('serial_number', $data['serial_number'])->exists()) {
                    throw new \Exception('Duplicate Serial Number');
                }

                // 2. Category Resolution
                $categoryName = $data['category'] ?? 'Uncategorized';
                $category = \App\Models\AssetCategory::firstOrCreate(['name' => $categoryName], ['is_electronic' => true]); // Smart create? Maybe risky but fast.

                // 3. User Resolution
                $userId = null;
                $status = 'Available';
                if (!empty($data['assigned_to_email'])) {
                    $user = \App\Models\User::where('email', $data['assigned_to_email'])->first();
                    if ($user) {
                        $userId = $user->id;
                        $status = 'Assigned';
                    }
                }

                $asset = Asset::create([
                    'tenant_id' => auth()->user()->tenant_id ?? 1,
                    'name' => $data['name'] ?? 'Imported Asset',
                    'serial_number' => $data['serial_number'],
                    'category_id' => $category->id,
                    'purchase_cost' => $data['purchase_cost'] ?? 0,
                    'purchase_date' => $this->parseDate($data['purchase_date'] ?? null),
                    'status' => $status
                ]);

                if ($userId) {
                    \App\Models\AssetAssignment::create([
                        'asset_id' => $asset->id,
                        'user_id' => $userId,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'ack_status' => 'Pending'
                    ]);
                }

                $success++;

            } catch (\Exception $e) {
                $errors[] = [
                    'row' => $index + 2, // +2 for header and 0-index
                    'error' => $e->getMessage(),
                    'data' => implode(',', $row)
                ];
            }
        }

        // Generate Error CSV if needed
        $errorUrl = null;
        if (count($errors) > 0) {
            $errorFileName = 'import_errors_' . time() . '.csv';
            $errorPath = storage_path('app/public/' . $errorFileName);
            $handle = fopen($errorPath, 'w');
            fputcsv($handle, ['Row', 'Error', 'Original Data']);
            foreach ($errors as $err) {
                fputcsv($handle, [$err['row'], $err['error'], $err['data']]);
            }
            fclose($handle);
            $errorUrl = asset('storage/' . $errorFileName);
        }

        return response()->json([
            'success_count' => $success,
            'failed_count' => count($errors),
            'error_url' => $errorUrl
        ]);
    }

    private function getCsvHeaders($path)
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        fclose($handle);
        return $header;
    }

    private function parseDate($date) {
        return $date ? date('Y-m-d', strtotime($date)) : null;
    }
}
