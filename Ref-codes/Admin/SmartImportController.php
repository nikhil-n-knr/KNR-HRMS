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
        $request->validate(['file' => 'required|file']);
        
        $file = $request->file('file');
        $path = $file->store('imports');
        
        $extension = $file->getClientOriginalExtension();
        if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load(storage_path('app/' . $path));
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            $headers = array_shift($rows);
        } else {
            $headers = $this->getCsvHeaders(storage_path('app/' . $path));
        }
        
        return response()->json([
            'path' => $path,
            'headers' => $headers,
            'db_fields' => [
                'name' => 'Resource Name',
                'category' => 'Category',
                'location' => 'Location',
                'serial_number' => 'Serial Tag',
                'vendor' => 'Vendor',
                'make' => 'Brand Name',
                'model' => 'Model',
                'purchase_cost' => 'Buying Cost',
                'purchase_date' => 'Entry Date',
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

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array(strtolower($extension), ['xlsx', 'xls'])) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
        } else {
            $rows = array_map('str_getcsv', file($path));
        }

        $header = array_shift($rows); 

        $map = $request->mapping; 
        
        $success = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                if (count($row) < 1 || empty(array_filter($row))) continue;

                $data = [];
                foreach ($map as $dbField => $csvIndex) {
                    if ($csvIndex !== null && isset($row[$csvIndex])) {
                        $data[$dbField] = trim($row[$csvIndex]);
                    }
                }
                
                if (empty($data['serial_number'])) {
                    throw new \Exception('Missing Serial Number');
                }
                if (Asset::where('serial_number', $data['serial_number'])->exists()) {
                    throw new \Exception('Duplicate Serial Number');
                }

                $categoryName = $data['category'] ?? 'Uncategorized';
                $category = \App\Models\AssetCategory::firstOrCreate(['name' => $categoryName], ['is_electronic' => true]);

                $locationId = null;
                if (!empty($data['location'])) {
                    $location = \App\Models\Location::firstOrCreate(['name' => $data['location']]);
                    $locationId = $location->id;
                }

                $vendorId = null;
                if (!empty($data['vendor'])) {
                    $vendor = \App\Models\Vendor::firstOrCreate(['name' => $data['vendor']]);
                    $vendorId = $vendor->id;
                }

                $userId = null;
                $status = 'Available';
                if (!empty($data['assigned_to_email'])) {
                    $user = \App\Models\User::where('email', $data['assigned_to_email'])->first();
                    if ($user) {
                        $userId = $user->id;
                        $status = 'Assigned';
                    }
                }

                $meta = [];
                if (!empty($data['make'])) $meta['make'] = $data['make'];
                if (!empty($data['model'])) $meta['model'] = $data['model'];

                $asset = Asset::create([
                    'tenant_id' => auth()->user()->tenant_id ?? 1,
                    'name' => $data['name'] ?? 'Imported Asset',
                    'serial_number' => $data['serial_number'],
                    'category_id' => $category->id,
                    'location_id' => $locationId,
                    'vendor_id' => $vendorId,
                    'purchase_cost' => $data['purchase_cost'] ?? 0,
                    'purchase_date' => $this->parseDate($data['purchase_date'] ?? null),
                    'status' => $status,
                    'quantity' => 1,
                    'meta' => count($meta) > 0 ? json_encode($meta) : null
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
                    'row' => $index + 2, 
                    'error' => $e->getMessage(),
                    'data' => implode(',', array_filter($row))
                ];
            }
        }

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

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Resource Name',
            'Category',
            'Location',
            'Serial Tag',
            'Vendor',
            'Brand Name',
            'Model',
            'Buying Cost',
            'Entry Date'
        ];

        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
        }

        $categories = \App\Models\AssetCategory::pluck('name')->toArray();
        $locations = \App\Models\Location::pluck('name')->toArray();
        $vendors = \App\Models\Vendor::pluck('name')->toArray();

        $hiddenSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'DropdownLists');
        $spreadsheet->addSheet($hiddenSheet);

        foreach ($categories as $index => $name) {
            $hiddenSheet->setCellValue('A' . ($index + 1), $name);
        }
        foreach ($locations as $index => $name) {
            $hiddenSheet->setCellValue('B' . ($index + 1), $name);
        }
        foreach ($vendors as $index => $name) {
            $hiddenSheet->setCellValue('C' . ($index + 1), $name);
        }

        $hiddenSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        for ($row = 2; $row <= 101; $row++) {
            if (count($categories) > 0) {
                $validation = $sheet->getCell('B' . $row)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1('DropdownLists!$A$1:$A$' . count($categories));
            }

            if (count($locations) > 0) {
                $validation = $sheet->getCell('C' . $row)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1('DropdownLists!$B$1:$B$' . count($locations));
            }

            if (count($vendors) > 0) {
                $validation = $sheet->getCell('E' . $row)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1('DropdownLists!$C$1:$C$' . count($vendors));
            }
        }

        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        return response()->stream(
            function () use ($writer) {
                $writer->save('php://output');
            },
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="smart_asset_import_template.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    private function getCsvHeaders($path)
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        fclose($handle);
        return $header;
    }

    private function parseDate($date) {
        if (!$date) return null;
        try {
            return date('Y-m-d', strtotime($date));
        } catch (\Exception $e) {
            return null;
        }
    }
}
