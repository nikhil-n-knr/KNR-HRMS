<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\ContactImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\CRM\ContactImportService; // Placeholder for service

class ContactImportController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $imports = ContactImport::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(10);
            
        return response()->json($imports);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB
        ]);

        $file = $request->file('file');
        // Store locally
        $path = $file->store('crm/imports', 'local'); 

        $import = ContactImport::create([
            'tenant_id' => auth()->user()->tenant_id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'imported_by' => auth()->id(), 
            'status' => 'pending',
        ]);

        // Dispatch job
        \App\Jobs\CRM\ProcessContactImport::dispatch($import);
        
        return response()->json(['message' => 'Import queued', 'import' => $import], 201);
    }

    /**
     * Download sample import template
     */
    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="crm_contact_import_template.csv"',
        ];

        $callback = function() {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'first_name', 'last_name', 'email', 'phone', 'title', 
                'account_name', 'industry', 'website', 
                'mobile', 'whatsapp', 'linkedin', 'facebook'
            ]);
            fputcsv($handle, [
                'John', 'Doe', 'john@example.com', '1234567890', 'Managing Director', 
                'Global Tech', 'Software', 'https://example.com',
                '0987654321', '123456789', 'linkedin.com/in/johndoe', ''
            ]);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
