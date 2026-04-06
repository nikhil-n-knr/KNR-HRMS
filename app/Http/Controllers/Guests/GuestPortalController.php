<?php

namespace App\Http\Controllers\Guests;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VisitorPass;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GuestPortalController extends Controller
{
    public function preCheckIn(Request $request, VisitorPass $pass)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'This invitation link has expired.');
        }

        $pass->load(['visitor.host', 'visitor']);
        
        // Fetch Purpose Config to know what fields to show
        // Assuming purpose_id is on visitor? Or pass? 
        // We stored purpose_id on visitor in checkIn, but invite might not have set it on visitor if it's new.
        // Let's assume invite sets it or we pass it.
        // For now, we'll try to find purpose from visitor or default.
        
        $purpose = DB::table('visitor_purposes')->where('id', $pass->visitor->purpose_id)->first();
        if (!$purpose) {
            // Fallback or find generic
            $purpose = (object) ['name' => 'General Visit', 'form_config' => '{}'];
        }

        return Inertia::render('Guests/PreCheckIn', [
            'pass' => $pass,
            'visitor' => $pass->visitor,
            'host' => $pass->visitor->host,
            'purpose' => $purpose,
            'config' => json_decode($purpose->form_config, true)
        ]);
    }

    public function update(Request $request, VisitorPass $pass)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Link expired.');
        }

        $request->validate([
            'photo_path' => 'nullable|string', // Base64 or uploaded path handling?
            // Add other validations dynamic
        ]);
        
        // Update Visitor
        $pass->visitor->update([
            'photo_path' => $request->photo_path,
            'company' => $request->company,
            'phone' => $request->phone,
        ]);
        
        $pass->update([
            'status' => 'Details-Completed', 
            'nda_status' => 'Signed',
            'meta_data' => array_merge($pass->meta_data ?? [], [
                'completed_at' => now()->toDateTimeString(),
                'pre_registered' => true
            ])
        ]);

        return back()->with('success', 'Registration Completed!');
    }
}
