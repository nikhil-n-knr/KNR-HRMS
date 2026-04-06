<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetScanController extends Controller
{
    /**
     * Handle the QR Scan.
     * Central entry point for physical tags.
     */
    public function handle(Request $request, Asset $asset)
    {
        // 1. Ensure User is Logged In
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 2. Route based on Role
        // If Admin or Manager (has permission to view all assets)
        if ($user->hasPermission('assets.view') || $user->hasRole('Admin') || $user->hasRole('Super Admin')) {
            return redirect()->route('admin.assets.show', $asset->id);
        }

        // 3. If Employee
        // Check if assigned to them? Or just allow viewing details?
        // Assuming transparency, employees can view asset details even if not assigned (e.g. to report issue on a shared printer)
        return redirect()->route('employee.assets.show', $asset->id);
    }
}
