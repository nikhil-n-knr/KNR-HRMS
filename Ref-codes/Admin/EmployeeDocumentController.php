<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\Documents\DocumentRegistryService;
use App\Services\Infrastructure\LoggerService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class EmployeeDocumentController extends Controller
{
    use ApiResponser;

    protected $logger;
    protected $registry;

    public function __construct(LoggerService $logger, DocumentRegistryService $registry)
    {
        $this->logger = $logger;
        $this->registry = $registry;
    }

    public function index(Request $request, $employeeId)
    {
        $query = EmployeeDocument::where('employee_id', $employeeId);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $documents = $query->latest()->get();
        
        return $this->success($documents);
    }

    public function store(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100', // Relaxed validation for custom categories
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120', // Max 5MB
            'metadata' => 'nullable|array', // Accept metadata
        ]);

        $file = $request->file('file');
        
        // Use Registry Service
        $document = $this->registry->file($employee, $file, [
            'title' => $validated['title'],
            'category' => $validated['category'], // Pass exact category string
            'type' => 'Uploaded',
            'source' => 'Manual',
            'is_system' => false,
            'meta' => $validated['metadata'] ?? [] // Pass metadata
        ]);

        $this->logger->log('employee_management', 'update', "Uploaded document for {$employee->first_name}: {$document->title}");

        return $this->success($document, 'Document uploaded successfully', 201);
    }

    public function destroy($employeeId, $id)
    {
        $document = EmployeeDocument::where('employee_id', $employeeId)->findOrFail($id);
        
        if ($document->is_system_generated) {
            return $this->error('Cannot delete system generated document manually.', 403);
        }

        $this->registry->delete($document);
        
        $this->logger->log('employee_management', 'update', "Deleted document: {$document->title}");

        return $this->success(null, 'Document deleted');
    }

    /**
     * Stream the file securely using ID (Obfuscates Path).
     */
    public function stream(\Illuminate\Http\Request $request, $id)
    {
        $document = EmployeeDocument::findOrFail($id);
        $user = auth()->user();
        
        // Authorization: Owner or Admin
        $isOwner = $document->employee->user_id === $user->id;
        $hasPermission = $user->hasPermission('employee_management.employees.view');
        
        if (!$isOwner && !$hasPermission) {
            abort(403, 'Unauthorized');
        }

        // Determine local path from map or fallback to the file_path itself
        $localPath = $document->storageMap ? $document->storageMap->local_path : $document->file_path;

        // Check if file exists locally in 'local' or 'public' disks
        $disk = 'local';
        if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($localPath)) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($localPath)) {
                $disk = 'public';
            } else {
                // If local file is missing, try redirecting to the S3 URL
                if (filter_var($document->file_path, FILTER_VALIDATE_URL)) {
                    return redirect()->away($document->file_path);
                }
                if ($document->storageMap && $document->storageMap->s3_url) {
                    return redirect()->away($document->storageMap->s3_url);
                }
                abort(404, 'File not found locally or on S3.');
            }
        }

        $baseDir = $disk === 'public' ? 'app/public/' : 'app/';
        return response()->file(storage_path($baseDir . $localPath), [
            'Cache-Control' => 'private, max-age=3600',
            'Content-Type' => $document->file_type,
        ]);
    }
}
