<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Location; // Assuming Location model exists in root or Admin namespace, checking..
// It's App\Models\Location based on previous context, but let's double check imports if needed.
// Actually, previous files used App\Http\Controllers\Admin\LocationController which suggests App\Models\Location exists.
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Assets/Audit/Run', [
            'locations' => \App\Models\Location::select('id', 'name')->orderBy('name')->get(),
            'categories' => \App\Models\AssetCategory::select('id', 'name')->get()
        ]);
    }

    public function fetchExpected(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'category_id' => 'nullable|exists:asset_categories,id'
        ]);

        $query = Asset::where('location_id', $request->location_id)
                      ->where('status', '!=', 'Scrapped')
                      ->where('status', '!=', 'Lost');

        if ($request->category_id) {
            $query->where('asset_category_id', $request->category_id);
        }

        $assets = $query->get(['id', 'asset_tag', 'name', 'serial_number', 'status']);

        return response()->json($assets);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'scanned_ids' => 'present|array',
            'missing_ids' => 'present|array'
        ]);

        $scannedCount = count($request->scanned_ids);
        $missingCount = count($request->missing_ids);
        $total = $scannedCount + $missingCount;
        $accuracy = $total > 0 ? round(($scannedCount / $total) * 100, 1) : 0;

        // 1. Create Session
        $session = \App\Models\AuditSession::create([
            'location_id' => $request->location_id,
            'auditor_id' => auth()->id(),
            'stats' => [
                'total' => $total,
                'scanned' => $scannedCount,
                'missing' => $missingCount,
                'accuracy' => $accuracy
            ],
            'status' => 'Completed'
        ]);

        // 2. Log Items & Update Assets
        // Scanned (Found)
        $scannedItems = [];
        foreach ($request->scanned_ids as $id) {
            $scannedItems[] = [
                'audit_session_id' => $session->id,
                'asset_id' => $id,
                'status' => 'Found',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        \App\Models\AuditItem::insert($scannedItems);
        
        Asset::whereIn('id', $request->scanned_ids)->update([
            'last_audited_at' => now(),
            'status' => 'Available'
        ]);

        // Missing
        $missingItems = [];
        foreach ($request->missing_ids as $id) {
            $missingItems[] = [
                'audit_session_id' => $session->id,
                'asset_id' => $id,
                'status' => 'Missing',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        if (!empty($missingItems)) {
             \App\Models\AuditItem::insert($missingItems);
        }

        Asset::whereIn('id', $request->missing_ids)->update([
            'status' => 'Missing',
            'notes' => 'Marked Missing during Audit #' . $session->id
        ]);

        return back()->with('success', 'Audit Saved. Session ID: ' . $session->id);
    }

    // List Previous Audits
    public function history()
    {
        return Inertia::render('Admin/Assets/Audit/History', [
            'sessions' => \App\Models\AuditSession::with(['location', 'auditor'])
                ->latest()
                ->paginate(10)
        ]);
    }

    // PDF Report
    public function downloadReport($id)
    {
        $session = \App\Models\AuditSession::with(['location', 'auditor', 'items.asset'])->findOrFail($id);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.audit', ['session' => $session]);
        
        return $pdf->download('audit-report-' . $session->id . '.pdf');
    }
}
