<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::withCount(['purchaseRequests', 'assets'])
            ->latest()
            ->paginate(10);

        // Enrich with mock scoring metrics for Scorecard UI
        $vendors->getCollection()->transform(function($vendor) {
           $vendor->avg_repair_cost    = rand(100, 500);
           $vendor->avg_turnaround_time = rand(24, 72);
           $vendor->score              = rand(70, 100);
           return $vendor;
        });

        return Inertia::render('Admin/Vendors/Scorecard', [
            'vendors' => $vendors
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string',
            'category'           => 'nullable|string',
            'gstin'              => 'nullable|string',
            'contact_person'     => 'nullable|string',
            'email'              => 'nullable|email',
            'phone'              => 'nullable|string',
            'sla_response_hours' => 'required|integer|min:1',
            'contract_end_date'  => 'nullable|date',
            'status'             => 'required|boolean',
        ]);

        $data['is_active'] = $request->boolean('status');
        unset($data['status']);

        Vendor::create($data);

        return back()->with('success', 'Vendor Added Successfully');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'name'               => 'required|string',
            'category'           => 'nullable|string',
            'gstin'              => 'nullable|string',
            'contact_person'     => 'nullable|string',
            'email'              => 'nullable|email',
            'phone'              => 'nullable|string',
            'sla_response_hours' => 'required|integer|min:1',
            'contract_end_date'  => 'nullable|date',
            'status'             => 'required|boolean',
        ]);

        $data['is_active'] = $request->boolean('status');
        unset($data['status']);

        $vendor->update($data);

        return back()->with('success', 'Vendor Updated Successfully');
    }

    public function destroy(Vendor $vendor)
    {
        if ($vendor->purchaseRequests()->exists() || $vendor->assets()->exists()) {
            return back()->with('error', 'Cannot delete vendor with associated purchase requests or assets.');
        }

        $vendor->delete();

        return back()->with('success', 'Vendor Deleted Successfully');
    }
}
