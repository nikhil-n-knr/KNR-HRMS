<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\VisitorPass;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HostApprovalController extends Controller
{
    public function index()
    {
        // Show pending visitors for CURRENT USER
        $pending = VisitorPass::with(['visitor'])
            ->whereHas('visitor', function($q) {
                $q->where('host_id', auth()->id());
            })
            ->where('status', 'Pending-Approval') // New Status
            ->latest()
            ->get();

        return Inertia::render('Host/Approvals/Index', [
             'pending_visits' => $pending
        ]);
    }

    public function action(Request $request, VisitorPass $pass)
    {
        // Security Check
        if ($pass->visitor->host_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject,wait'
        ]);

        if ($request->action === 'approve') {
             $pass->update([
                 'status' => 'Checked-In', // Or 'Approved' -> Badge Print triggers
                 'check_in_at' => now(), // Assume immediate entry upon approval? Or just 'Approved'
                 'host_vouched' => true
             ]);
             // Notify Kiosk (Pusher/Polling)
        } elseif ($request->action === 'reject') {
             $pass->update(['status' => 'Rejected']);
        } elseif ($request->action === 'wait') {
             // Just send notification, keep status Pending
             // $pass->update(['meta_data->wait_message' => '5 mins']);
        }

        return back()->with('success', 'Action recorded.');
    }
}
