<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /**
     * Serve a private file securely.
     */
    public function show(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            abort(404);
        }

        // 1. Security Check: Tenant Isolation
        // Path starts with: tenants/{id}/...
        $tenantId = auth()->user()->tenant_id ?? 'system'; 
        
        // Strict Check: User can only access their own tenant's folder
        if (!str_starts_with($path, "tenants/{$tenantId}/")) {
            abort(403, 'Unauthorized access to file.');
        }

        // 2. Existence Check
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        // 3. Serve with Caching Headers (Performance)
        // Cache for 1 hour
        return response()->file(storage_path("app/{$path}"), [
            'Cache-Control' => 'private, max-age=3600',
            'Access-Control-Allow-Origin' => '*', // For Vue XHR if needed
        ]);
    }
}
