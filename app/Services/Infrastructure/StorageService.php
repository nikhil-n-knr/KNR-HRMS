<?php

namespace App\Services\Infrastructure;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StorageService
{
    protected $defaultDisk;

    public function __construct()
    {
        $this->defaultDisk = config('filesystems.default', 'local');
    }

    /**
     * Upload a file or raw content.
     *
     * @param UploadedFile|string $content
     * @param string $path Directory path
     * @param string|null $name Optional filename
     * @param string|null $disk
     * @return string The stored file path
     */
    public function upload($content, string $path, ?string $name = null, ?string $disk = null): string
    {
        $disk = $disk ?? $this->defaultDisk;
        
        if ($content instanceof UploadedFile) {
            if ($name) {
                return $content->storeAs($path, $name, $disk);
            }
            return $content->store($path, $disk);
        }

        // Handle raw string content
        $filename = $name ?? Str::random(40) . '.txt';
        $fullPath = rtrim($path, '/') . '/' . $filename;
        
        Storage::disk($disk)->put($fullPath, $content);
        
        return $fullPath;
    }

    /**
     * Get the public accessible URL of a file.
     */
    public function url(string $path, ?string $disk = null): string
    {
        $disk = $disk ?? $this->defaultDisk;
        return Storage::disk($disk)->url($path);
    }

    /**
     * Get the raw content of a file.
     */
    public function get(string $path, ?string $disk = null): ?string
    {
        $disk = $disk ?? $this->defaultDisk;
        
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->get($path);
        }
        
        return null;
    }

    /**
     * Delete a file.
     */
    public function delete(string $path, ?string $disk = null): bool
    {
        $disk = $disk ?? $this->defaultDisk;
        return Storage::disk($disk)->delete($path);
    }

    /**
     * Upload a private file (e.g. employee documents).
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $subfolder
     * @param string $category
     * @return string The stored file path
     */
    public function uploadPrivate(UploadedFile $file, string $folder, string $subfolder, string $category): string
    {
        $path = "private/{$folder}/{$subfolder}/{$category}";
        $filename = time() . '_' . $file->getClientOriginalName();
        
        return $file->storeAs($path, $filename, $this->defaultDisk);
    }

    /**
     * Delete a private file.
     */
    public function deletePrivate(string $path): bool
    {
        return $this->delete($path);
    }
}
