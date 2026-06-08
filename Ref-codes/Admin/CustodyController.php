<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PhysicalRecord;
use Carbon\Carbon;
use Inertia\Inertia;

class CustodyController extends Controller
{
    /**
     * Display the Chain of Custody / Movement Log.
     */
    public function index()
    {
        // Fetch all documents currently with an employee
        $custodyItems = PhysicalRecord::with(['user:id,name', 'location:id,name'])
            ->where('status', 'With_Employee')
            ->orderBy('updated_at', 'asc') // Oldest checkouts first (High Risk)
            ->get()
            ->map(function ($item) {
                $checkoutTime = Carbon::parse($item->updated_at);
                $durationHours = $checkoutTime->diffInHours(now());
                
                return [
                    'id' => $item->id,
                    'document_type' => $item->document_type,
                    'user_name' => $item->user ? $item->user->name : ($item->outsider_name ?? 'Unknown'),
                    'location_name' => $item->location->name,
                    'checkout_at' => $item->updated_at->toIso8601String(),
                    'duration_hours' => $durationHours,
                    'is_high_risk' => $durationHours > 24,
                    'container_ref' => $item->container_ref
                ];
            });

        return Inertia::render('Admin/Documents/MovementLog', [
            'custody_items' => $custodyItems
        ]);
    }
}
