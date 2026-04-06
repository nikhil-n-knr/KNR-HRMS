<?php

namespace App\Services\Documents;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\Infrastructure\StorageService;
use Illuminate\Http\UploadedFile;

class DocumentRegistryService
{
    protected $storage;

    public function __construct(StorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * File a document to the Central Hub.
     *
     * @param Employee $employee The owner
     * @param UploadedFile $file The file
     * @param array $options [category, type, source, reference, meta]
     */
    public function file(Employee $employee, UploadedFile $file, array $options = []): EmployeeDocument
    {
        $category = $options['category'] ?? 'General';
        $type = $options['type'] ?? 'Document';
        $source = $options['source'] ?? 'Manual';
        $reference = $options['reference'] ?? null;
        $meta = $options['meta'] ?? [];
        $isSystem = $options['is_system'] ?? false;

        // 1. Determine Storage Path
        // tenants/{id}/private/employees/{code}/{category_slug}/{filename}
        // Organizing physically by category helps admin browsing
        $categorySlug = \Illuminate\Support\Str::slug($category);
        
        $storedPath = $this->storage->uploadPrivate(
            $file,
            'employees',
            $employee->employee_code,
            $categorySlug
        );

        // 2. Create Database Record
        return $employee->documents()->create([
            'title' => $options['title'] ?? $file->getClientOriginalName(),
            'category' => $category,
            'document_type' => $type,
            'file_path' => $storedPath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => round($file->getSize() / 1024, 2) . ' KB',
            'is_system_generated' => $isSystem,
            'source_module' => $source,
            'source_reference' => $reference,
            'metadata' => $meta,
            'uploaded_by' => auth()->id()
        ]);
    }

    /**
     * Delete a document.
     */
    public function delete(EmployeeDocument $document): bool
    {
        // 1. Physically Move to Trash (via StorageService)
        $this->storage->deletePrivate($document->file_path);

        // 2. Remove Record
        return $document->delete();
    }
}
