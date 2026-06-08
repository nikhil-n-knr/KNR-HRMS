<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Visitor;
use App\Models\VisitorPass;
use App\Services\VisitorRoutingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->boolean('showArchived');

        $query = $showArchived ? VisitorPass::onlyTrashed() : VisitorPass::query();
        $eventsQuery = $showArchived ? Event::onlyTrashed() : Event::query();

        // 1. Apply Filters
        if ($request->filled('status') && $request->status !== 'All Statuses') {
            $query->where('status', $request->status);
        }

        if ($request->filled('category') && $request->category !== 'All Categories') {
            $query->where('meta_data->purpose_name', $request->category);
        }

        if ($request->filled('dateRange')) {
            switch ($request->dateRange) {
                case 'today':
                    $query->where(function($q) {
                        $q->whereDate('check_in_at', today())
                          ->orWhereDate('visit_date', today());
                    });
                    break;
                case 'tomorrow':
                    $query->whereDate('visit_date', today()->addDay());
                    break;
                case '7 days':
                    $query->where('check_in_at', '>=', now()->subDays(7));
                    break;
                case 'custom':
                    if ($request->filled('startDate')) {
                        $query->whereDate('visit_date', '>=', $request->startDate);
                    }
                    if ($request->filled('endDate')) {
                        $query->whereDate('visit_date', '<=', $request->endDate);
                    }
                    break;
            }
        }

        return Inertia::render('Admin/Visitors/Index', [
            'filters' => $request->all(['dateRange', 'status', 'category', 'startDate', 'endDate', 'showArchived']),
            'stats' => [
                'currently_inside' => VisitorPass::whereNotNull('check_in_at')->whereNull('check_out_at')->where('status', '!=', 'Revoked')->count(),
                'expected_today' => VisitorPass::whereDate('visit_date', today())->whereNull('check_in_at')->where('status', '!=', 'Revoked')->count(),
                'visited_this_week' => VisitorPass::whereBetween('visit_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->count(),
                'visited_this_month' => VisitorPass::whereMonth('visit_date', now()->month)->whereYear('visit_date', now()->year)->count(),
                'busiest_hour' => VisitorPass::selectRaw('HOUR(check_in_at) as hour, count(*) as count')
                    ->whereNotNull('check_in_at')
                    ->groupBy('hour')
                    ->orderByDesc('count')
                    ->first()->hour ?? 'N/A',
                'pulse' => [
                    'active_violations' => VisitorPass::where('status', 'Overstayed')->count(),
                    'security_alerts' => DB::table('visitor_access_logs')->whereDate('created_at', today())->count(),
                    'unacknowledged' => VisitorPass::where('status', 'Host-Approval-Pending')->count(),
                    'traffic_delta' => VisitorPass::whereDate('created_at', today())->whereNotNull('check_in_at')->count() - 
                                     VisitorPass::whereDate('created_at', today())->whereNotNull('check_out_at')->count()
                ]
            ],
            'visitors_inside' => VisitorPass::with('visitor.host')
                ->whereNotNull('check_in_at')
                ->whereNull('check_out_at')
                ->where('status', '!=', 'Revoked')
                ->latest('check_in_at')
                ->get(),
            'all_passes' => $query->with('visitor.host')->latest()->get(),
            'events' => $eventsQuery->with('organizer')->withCount('passes')->latest()->get()->filter(function ($event) use ($showArchived) {
                if ($showArchived) {
                    return true;
                }
                return is_null($event->guest_limit) || $event->passes_count < $event->guest_limit;
            })->values(),
            'employees' => \App\Models\Employee::with(['user', 'department'])->get()
                ->filter(function ($emp) {
                    return $emp->user; // Only include employees with a linked user
                })
                ->map(function ($emp) {
                    return [
                        'id' => $emp->user_id, 
                        'name' => $emp->user->name,
                        'department' => $emp->department ? $emp->department->name : 'General'
                    ];
                })->values(), // Reset keys
            'dependents' => \App\Models\EmployeeFamily::with('employee.user')->get(),
            'analytics_chart' => VisitorPass::selectRaw('DATE(check_in_at) as date, count(*) as count')
                ->where('check_in_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'purposes' => DB::table('visitor_purposes')->where('is_active', true)->get(),
            'analytics' => [
                'overstay_stats' => [
                    'on_time' => VisitorPass::where('status', 'Checked-Out')->whereRaw('TIMESTAMPDIFF(MINUTE, check_in_at, check_out_at) <= expected_duration')->count(),
                    'overstayed' => VisitorPass::where('status', 'Overstayed')->orWhere(function($q) {
                        $q->where('status', 'Checked-Out')->whereRaw('TIMESTAMPDIFF(MINUTE, check_in_at, check_out_at) > expected_duration');
                    })->count(),
                ],
                'facility_heatmap' => VisitorPass::selectRaw('HOUR(check_in_at) as hour, count(*) as count')
                    ->whereNotNull('check_in_at')
                    ->where('check_in_at', '>=', now()->subDays(30))
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get(),
                'host_responsiveness' => VisitorPass::whereNotNull('host_response_time')
                    ->avg('host_response_time') ?? 0,
                'denied_logs' => DB::table('visitor_access_logs')
                    ->latest()
                    ->limit(20)
                    ->get(),
                'top_insights' => [
                    'frequent_visitors' => Visitor::withCount('passes')
                        ->orderByDesc('passes_count')
                        ->limit(5)
                        ->get(),
                    'most_active_hosts' => \App\Models\User::withCount('visitorPasses')
                        ->orderByDesc('visitor_passes_count')
                        ->limit(5)
                        ->get()
                ]
            ]
        ]);
    }

    // Tab A: Reception Console - Check In (Smart Logic)
    public function checkIn(Request $request)
    {
        // 1. Basic Validation
        $rules = [
            'name' => 'required',
            'host_id' => 'required|exists:users,id',
            'purpose_id' => 'required|exists:visitor_purposes,id', // ID from dropdown
            // Dynamic validations handled below
        ];
        
        $request->validate($rules);

        // 2. Delegate to Workflow Service
        $routingService = app(VisitorRoutingService::class);
        
        try {
            $pass = $routingService->processWalkIn($request->all());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }


        return back()->with('success', "Check-in Complete. Badge Printed.")
                     ->with('print_id', $pass->id);
    }

    public function invite(Request $request) 
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'purpose_id' => 'required|exists:visitor_purposes,id',
            'visit_date' => 'required',
            'subject' => 'required|string',
            'body' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
        ]);
        
        // 1. Create/Update Visitor
        $visitor = Visitor::updateOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name,
                'phone' => $request->phone,
                'host_id' => auth()->id(),
            ]
        );
        
        $purpose = DB::table('visitor_purposes')->where('id', $request->purpose_id)->first();
        
        // 2. Create Pre-Registered Pass
        $pass = VisitorPass::create([
            'visitor_id' => $visitor->id,
            'pass_code' => Str::upper(Str::random(8)),
            'status' => 'Pre-Registered',
            'visit_date' => $request->visit_date,
            'meta_data' => [
                'invitation' => true,
                'purpose_name' => $purpose->name ?? 'General'
            ]
        ]);
        
        // Send Email
        try {
            $cc = array_filter(array_map('trim', explode(',', $request->cc)));
            $bcc = array_filter(array_map('trim', explode(',', $request->bcc)));

            $mail = \Illuminate\Support\Facades\Mail::to($request->email);
            if (!empty($cc)) {
                $mail->cc($cc);
            }
            if (!empty($bcc)) {
                $mail->bcc($bcc);
            }

            $mail->send(new \App\Mail\VisitorInvitation($pass, $request->subject, $request->body));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Visitor invitation mail send failed: ' . $e->getMessage());
        }
        
        // 3. Return data for frontend modal (flash)
        return back()->with('flash', [
            'invitation_data' => [
                'link' => route('visitors.guest.pre-checkin', $pass->pass_code),
                'qr' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . $pass->pass_code,
                'name' => $visitor->name
            ]
        ]);
    }

    public function storeEvent(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'organizer_id' => auth()->id(),
            'guest_limit' => $request->guest_limit,
            'access_areas' => $request->access_areas
        ]);

        return back()->with('success', 'Event Created Successfully.');
    }

    public function checkOut(Request $request, $id)
    {
        $pass = VisitorPass::findOrFail($id);
        
        if ($pass->check_out_at) {
            return back()->withErrors(['error' => 'This visitor has already been checked out.']);
        }

        $pass->update([
            'check_out_at' => now(),
            'status' => 'Checked-Out',
            'badge_collected_at' => $request->boolean('badge_collected') ? now() : null,
        ]);

        return back()->with('success', 'Visitor checked out successfully.');
    }

    public function printBadge($id)
    {
        $pass = VisitorPass::with('visitor.host')->findOrFail($id);
        return view('admin.visitors.badge_template', compact('pass'))->with('visitor', $pass->visitor);
    }

    public function export(Request $request)
    {
        $query = VisitorPass::query();

        if ($request->filled('status') && $request->status !== 'All Statuses') {
            $query->where('status', $request->status);
        }

        if ($request->filled('category') && $request->category !== 'All Categories') {
            $query->where('meta_data->purpose_name', $request->category);
        }

        if ($request->filled('dateRange')) {
            switch ($request->dateRange) {
                case 'today':
                    $query->where(function($q) {
                        $q->whereDate('check_in_at', today())
                          ->orWhereDate('visit_date', today());
                    });
                    break;
                case 'tomorrow':
                    $query->whereDate('visit_date', today()->addDay());
                    break;
                case '7 days':
                    $query->where('check_in_at', '>=', now()->subDays(7));
                    break;
                case 'custom':
                    if ($request->filled('startDate')) {
                        $query->whereDate('visit_date', '>=', $request->startDate);
                    }
                    if ($request->filled('endDate')) {
                        $query->whereDate('visit_date', '<=', $request->endDate);
                    }
                    break;
            }
        }

        $passes = $query->with('visitor.host')->latest()->get();
        $csvHeader = ['ID', 'Visitor Name', 'Email', 'Phone', 'Company', 'Whom to Meet', 'Date of Visit', 'Check-In Time', 'Check-Out Time', 'Reason for Visit', 'Status'];
        
        $callback = function() use ($passes, $csvHeader) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeader);
            foreach ($passes as $pass) {
                fputcsv($file, [
                    $pass->pass_code,
                    $pass->visitor->name ?? 'N/A',
                    $pass->visitor->email ?? 'N/A',
                    $pass->visitor->phone ?? 'N/A',
                    $pass->visitor->company ?? 'N/A',
                    $pass->visitor->host->name ?? 'N/A',
                    $pass->visit_date ?? 'N/A',
                    $pass->check_in_at ? $pass->check_in_at->toDateTimeString() : 'N/A',
                    $pass->check_out_at ? $pass->check_out_at->toDateTimeString() : 'N/A',
                    $pass->meta_data['purpose_name'] ?? 'General',
                    $pass->status
                ]);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'visitors_report_' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function updateVisitorPass(Request $request, $id)
    {
        $pass = VisitorPass::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'company' => 'nullable|string',
            'host_id' => 'required|exists:users,id',
            'expected_duration' => 'required|integer',
        ]);

        $visitor = $pass->visitor;
        $visitor->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'company' => $request->company,
            'host_id' => $request->host_id,
        ]);

        $pass->update([
            'expected_duration' => $request->expected_duration,
        ]);

        return back()->with('success', 'Visitor Details Updated Successfully.');
    }

    public function restorePass($id)
    {
        $pass = VisitorPass::onlyTrashed()->findOrFail($id);
        $pass->restore();
        return back()->with('success', 'Pass Restored Successfully.');
    }

    public function restoreEvent($id)
    {
        $event = Event::onlyTrashed()->findOrFail($id);
        $event->restore();
        return back()->with('success', 'Event Restored Successfully.');
    }

    public function forceDeletePass($id)
    {
        $pass = VisitorPass::withTrashed()->findOrFail($id);
        $pass->forceDelete();
        return back()->with('success', 'Pass Deleted Permanently.');
    }

    public function forceDeleteEvent($id)
    {
        $event = Event::withTrashed()->findOrFail($id);
        $event->forceDelete();
        return back()->with('success', 'Event Deleted Permanently.');
    }

    public function kiosk()
    {
        return Inertia::render('Admin/Visitors/Kiosk', [
            'employees' => \App\Models\Employee::with(['user', 'department'])->get()
                ->filter(function ($emp) {
                    return $emp->user;
                })
                ->map(function ($emp) {
                    return [
                        'id' => $emp->user_id, 
                        'name' => $emp->user->name,
                    ];
                })->values(),
            'purposes' => DB::table('visitor_purposes')->where('is_active', true)->get(),
        ]);
    }
    public function destroy($id)
    {
        $pass = VisitorPass::findOrFail($id);
        $pass->delete(); // Soft delete
        return back()->with('success', 'Pass Archived Successfully.');
    }

    public function destroyEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->delete(); // Soft delete
        return back()->with('success', 'Event Archived Successfully.');
    }

    public function getEventGuests($id)
    {
        $event = Event::with(['passes.visitor.host', 'organizer'])->findOrFail($id);
        
        // Add capacity stats
        $event->capacity_stats = app(\App\Services\EventService::class)->getCapacityStats($event);
        
        return response()->json($event);
    }

    public function importGuests(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240'
        ]);

        $event = Event::findOrFail($id);
        $path = $request->file('file')->store('imports');

        $count = app(\App\Actions\BulkGuestImporter::class)->execute($event, $path);

        return back()->with('success', "Processed {$count} guests and queued invitations.");
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'pass_ids' => 'required|array',
            'action' => 'required|string'
        ]);

        app(\App\Services\EventService::class)->bulkAction($request->pass_ids, $request->action);

        return back()->with('success', 'Bulk Action Executed Successfully.');
    }

    public function preCheckin($code)
    {
        $pass = VisitorPass::with(['visitor', 'event'])->where('pass_code', $code)->firstOrFail();
        
        return Inertia::render('Admin/Visitors/PreCheckin', [
            'pass' => $pass
        ]);
    }

    public function confirmCheckin(Request $request, $id)
    {
        $pass = VisitorPass::findOrFail($id);
        $pass->update([
            'status' => 'Checked-In',
            'check_in_at' => now(),
        ]);

        return back()->with('success', 'Check-in Confirmed. Welcome!');
    }

    public function wrapUpEvent($id)
    {
        $event = Event::findOrFail($id);
        app(\App\Services\EventService::class)->bulkCheckout($event);
        
        $event->update(['status' => 'Completed']);

        return back()->with('success', 'Event Wrapped Up. All guests checked out.');
    }

    public function getEventPerformance($id)
    {
        $event = Event::findOrFail($id);
        $report = app(\App\Services\EventService::class)->generatePerformanceReport($event);
        
        return response()->json($report);
    }
}
