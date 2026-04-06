<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\VisitorPurpose;
use App\Models\VisitorPass;
use App\Services\VisitorRoutingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KioskController extends Controller
{
    protected $routingService;

    public function __construct(VisitorRoutingService $routingService)
    {
        $this->routingService = $routingService;
    }
    public function standby()
    {
        return Inertia::render('Kiosk/Standby');
    }

    public function checkIn()
    {
        return Inertia::render('Kiosk/CheckIn', [
            'purposes' => VisitorPurpose::where('is_active', true)->get()
        ]);
    }

    public function scan()
    {
        return Inertia::render('Kiosk/Scan');
    }

    public function walkIn(Request $request) 
    {
        $request->validate(['purpose' => 'required|exists:visitor_purposes,id']);
        
        $purpose = VisitorPurpose::find($request->purpose);
        $config = json_decode($purpose->form_config, true) ?? [];
        
        return Inertia::render('Kiosk/Form', [
            'purpose' => $purpose,
            'config' => $config,
            // Pass minimal host list for selection (Name + Dept)
            'hosts' => \App\Models\Employee::with(['user', 'department'])->get()->map(function($emp) {
                return [
                    'id' => $emp->user_id, // Use User ID as host_id
                    'name' => $emp->user->name,
                    'department' => $emp->department ? $emp->department->name : ''
                ];
            })
        ]);
    }

    public function storeWalkIn(Request $request)
    {
        $data = $request->validate([
            'purpose_id' => 'required|exists:visitor_purposes,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'host_id' => 'required|exists:users,id',
            'company' => 'nullable|string',
            'photo_path' => 'nullable|string',
            'group_size' => 'integer|min:1',
            'nda_agreed' => 'accepted'
        ]);

        try {
            $pass = $this->routingService->processWalkIn($data);
            return to_route('kiosk.success', $pass->id);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function processScan(Request $request)
    {
        $request->validate(['code' => 'required']);

        // Find pass by code (assuming pass_code column)
        $pass = VisitorPass::with(['visitor.host'])->where('pass_code', $request->code)->first();

        if (!$pass) {
            return back()->withErrors(['code' => 'Invalid QR Code.']);
        }

        if ($pass->status === 'Checked-In') {
             return back()->withErrors(['code' => 'Already checked in.']);
        }

        // Fast Track Check-in
        $pass->update([
            'status' => 'Checked-In',
            'check_in_at' => now()
        ]);

        return to_route('kiosk.success', ['pass' => $pass->id]);
    }

    public function success(VisitorPass $pass)
    {
        $pass->load('visitor.host');
        return Inertia::render('Kiosk/Success', [
            'pass' => $pass,
            'visitor' => $pass->visitor,
            'host' => $pass->visitor->host
        ]);
    }
}
