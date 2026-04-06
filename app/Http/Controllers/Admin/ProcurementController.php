<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequest;
use App\Models\Asset;
use App\Models\Vendor;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ProcurementController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Assets/Procurement/Index', [
            'purchase_requests' => PurchaseRequest::with('vendor', 'createdBy')->latest()->paginate(10),
            'vendors' => Vendor::select('id', 'name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric'
        ]);

        $totalCost = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_cost']);
        $gstAmount = $totalCost * 0.18; // Default 18% GST assumption for MVP

        PurchaseRequest::create([
            'tenant_id' => auth()->user()->tenant_id ?? 1, // Fallback
            'vendor_id' => $request->vendor_id,
            'status' => 'Draft',
            'items' => $request->items,
            'total_cost' => $totalCost,
            'gst_amount' => $gstAmount,
            'created_by' => auth()->id(),
            'po_number' => 'PO-' . strtoupper(Str::random(8))
        ]);

        return back()->with('success', 'Purchase Request Created');
    }

    /**
     * Convert a "Received" Bill/PO into Assets
     */
    public function convertToAssets(Request $request, PurchaseRequest $pr)
    {
        // 1. Validate
        if ($pr->status === 'Converted') {
            return back()->with('error', 'This PO has already been converted.');
        }

        // 2. Loop Items
        $count = 0;
        foreach ($pr->items as $item) {
            $qty = $item['quantity'];
            
            for ($i = 0; $i < $qty; $i++) {
                Asset::create([
                    'tenant_id' => $pr->tenant_id,
                    'name' => $item['name'],
                    'vendor_id' => $pr->vendor_id,
                    'purchase_date' => now(), // Date of conversion
                    'purchase_cost' => $item['unit_cost'],
                    'status' => 'Available',
                    'serial_number' => 'AST-' . strtoupper(Str::random(6)), // Placeholder until physically tagged
                    'category_id' => 1, // Defaulting to 1 (General) for safety, should be mapped
                    'is_returnable' => true
                ]);
                $count++;
            }
        }

        // 3. Update PO Status
        $pr->update(['status' => 'Converted', 'invoice_no' => $request->invoice_no ?? $pr->invoice_no]);

        return back()->with('success', "Success! {$count} Assets created from PO.");
    }
}
